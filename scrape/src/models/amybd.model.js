/**
 * AmyBD Model
 * -----------
 * Converts the raw `Trips` array from the AmyBD API into the canonical
 * flight shape the rest of the system expects:
 *
 *   {
 *     departure: { airline, logo, departure, arrival, origin, destination, duration, stops },
 *     return:    null | { … same … },
 *     totalPrice,
 *     discountedPrice,
 *     currency,
 *   }
 *
 * FARE FIELD TRUTH (from Python reference script):
 *   fTFare   = total ORIGINAL  fare for ALL passengers  ← used as totalPrice
 *   fDTFare  = total DISCOUNTED fare for ALL passengers ← used as discountedPrice
 *   fFare    = adult original fare per pax
 *   fCFare   = child original fare per pax
 *   fIFare   = infant original fare per pax
 */

// ── Time helpers ──────────────────────────────────────────────────────────────

/**
 * AmyBD time strings arrive in ISO-like format or "HH:MM AM/PM".
 * We normalise everything to "HH:MM" (24-hour) so Carbon on the Laravel
 * side can parse it consistently.
 */
function parseTime(raw) {
  if (!raw) return null;

  // Already ISO datetime?  e.g. "2026-04-25T08:00:00"
  if (raw.includes("T")) {
    return raw.split("T")[1]?.substring(0, 5) || null;
  }

  // "08:00 AM" / "12:30 PM" etc.
  const ampm = raw.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
  if (ampm) {
    let h = parseInt(ampm[1], 10);
    const m = ampm[2];
    const period = ampm[3].toUpperCase();
    if (period === "AM" && h === 12) h = 0;
    if (period === "PM" && h !== 12) h += 12;
    return `${String(h).padStart(2, "0")}:${m}`;
  }

  // "HH:MM" already
  if (/^\d{1,2}:\d{2}$/.test(raw.trim())) return raw.trim();

  return raw;
}

/**
 * Convert AmyBD duration string (e.g. "1h 05m", "45m", "1h") to "Xh Ym".
 * The raw field `fDur` already looks like "1h 05m" in most cases.
 */
function formatDuration(raw, seconds) {
  if (raw && typeof raw === "string" && raw.trim()) return raw.trim();
  if (typeof seconds === "number" && seconds > 0) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
  }
  return null;
}

// ── Stops helper ──────────────────────────────────────────────────────────────

function stopsLabel(raw) {
  if (raw === 0 || raw === "0" || raw === "Non Stop") return "Non Stop";
  if (!raw) return "Non Stop";

  // If it's already a string like "1 Stop", return it directly
  if (typeof raw === "string" && raw.toLowerCase().includes("stop")) {
    return raw;
  }

  const count = parseInt(raw, 10);
  if (isNaN(count)) return raw;
  if (count === 0) return "Non Stop";
  return `${count} Stop${count > 1 ? "s" : ""}`;
}

// ── Trip → leg entry ──────────────────────────────────────────────────────────

function buildLeg(trip) {
  // Use the IATA code (stAirCode) to generate a reliable logo URL from Google Flights CDN
  const airlineCode = (trip.stAirCode || "").trim().toUpperCase();
  const logoUrl = airlineCode ? `https://www.gstatic.com/flights/airline_logos/70px/${airlineCode}.png` : null;

  return {
    airline:     trip.stAirline  || trip.stAirCode || "Unknown",
    logo:        logoUrl,
    flightNo:    trip.fNo        || null,
    departure:   parseTime(trip.fDTime),
    arrival:     parseTime(trip.fATime),
    origin:      trip.fFrom      || null,
    destination: trip.fDest      || null,
    duration:    formatDuration(trip.fDur, trip.fDursec),
    stops:       stopsLabel(trip.fStop || 0),
    aircraft:    trip.fModel     || null,
    baggage:     trip.fBag       || null,
    bookingClass:trip.fClsNam   || null,
    seatsLeft:   trip.fSeat      || null,
    refundable:  trip.fRefund    === "REFUND",
    // booking tokens (forwarded for any future deep-link / booking flow)
    fsoft:       trip.fSoft      || null,
    fGDSid:      trip.fGDSid     || null,
    fAMYid:      trip.fAMYid     || null,
  };
}

// ── Main format function ──────────────────────────────────────────────────────

/**
 * @param {object} raw   - Raw return value from amybd.service.scrapeFlights()
 * @param {object} params - Original search params (from controller)
 * @returns {{ flights: Array, search_id: string|null, isCompleted: boolean }}
 */
function formatFlightData(raw, params = {}) {
  if (!raw || !raw.trips) {
    return { flights: [], search_id: null, isCompleted: true };
  }

  const { trips, searchId, isCompleted, trip: tripType } = raw;
  const isRoundTrip = tripType === "RT" || !!params.returnDate;

  if (!isRoundTrip) {
    // ── One-Way ──────────────────────────────────────────────────────────────
    const flights = trips.map((t) => ({
      departure:       buildLeg(t),
      return:          null,
      totalPrice:      t.fTFare  || t.fFare  || 0,
      discountedPrice: t.fDTFare || t.fDFare || t.fTFare || 0,
      currency:        "BDT",
    }));

    flights.sort((a, b) => a.totalPrice - b.totalPrice);
    return { flights, search_id: searchId || null, isCompleted: !!isCompleted };
  }

  // ── Round-Trip ────────────────────────────────────────────────────────────
  // AmyBD marks return legs with fReturn == 1 (truthy).
  const outboundTrips = trips.filter((t) => !t.fReturn);
  const returnTrips   = trips.filter((t) =>  t.fReturn);

  // Group by fSoft so we only combine flights from the same fare family.
  const fsoftGroups = {};
  trips.forEach((t) => {
    const key = t.fSoft || "__default__";
    if (!fsoftGroups[key]) fsoftGroups[key] = { ob: [], rt: [] };
    if (t.fReturn) fsoftGroups[key].rt.push(t);
    else           fsoftGroups[key].ob.push(t);
  });

  const flights = [];
  for (const { ob, rt } of Object.values(fsoftGroups)) {
    for (const o of ob) {
      for (const r of rt) {
        flights.push({
          departure:       buildLeg(o),
          return:          buildLeg(r),
          totalPrice:      (o.fTFare  || 0) + (r.fTFare  || 0),
          discountedPrice: (o.fDTFare || 0) + (r.fDTFare || 0),
          currency:        "BDT",
        });
      }
    }
  }

  // If no pairings formed (unlikely), fall back to outbound-only
  if (flights.length === 0 && outboundTrips.length > 0) {
    outboundTrips.forEach((t) => {
      flights.push({
        departure:       buildLeg(t),
        return:          null,
        totalPrice:      t.fTFare  || 0,
        discountedPrice: t.fDTFare || 0,
        currency:        "BDT",
      });
    });
  }

  flights.sort((a, b) => a.totalPrice - b.totalPrice);
  return { flights, search_id: searchId || null, isCompleted: !!isCompleted };
}

module.exports = { formatFlightData };
