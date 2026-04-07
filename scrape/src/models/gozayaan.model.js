// ── Helpers ───────────────────────────────────────────────────────────────────

function formatDuration(minutes) {
  if (!minutes || minutes <= 0) return null;
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

/**
 * GoZayaan returns ISO datetime strings like "2026-04-14T10:15:00"
 * We extract just "HH:MM" so Carbon on the Laravel side can parse it.
 */
function formatTime(dateTime) {
  if (!dateTime) return null;
  return dateTime.split("T")[1]?.substring(0, 5) || null;
}

// ── Lookups ───────────────────────────────────────────────────────────────────

function buildLookups(result) {
  const carrierMap = {};
  (result.carriers || []).forEach((c) => {
    carrierMap[c.code] = { name: c.name, logo: c.logo || null };
  });

  const segmentMap = {};
  (result.segments || []).forEach((seg) => {
    segmentMap[seg.hash] = {
      origin: seg.origin,
      destination: seg.destination,
    };
  });

  const legMap = {};
  (result.legs || []).forEach((leg) => {
    const stops = (leg.lay_over_details || []).length;
    const segHashes = leg.segment_hashes || [];

    const firstSeg = segmentMap[segHashes[0]] || {};
    const lastSeg  = segmentMap[segHashes[segHashes.length - 1]] || {};

    legMap[leg.hash] = {
      departure:   formatTime(leg.departure_date_time),
      arrival:     formatTime(leg.arrival_date_time),
      duration:    formatDuration(leg.travel_time),
      stops:       stops === 0 ? "Non Stop" : `${stops} Stop${stops > 1 ? "s" : ""}`,
      carrier:     leg.marketing_carrier,
      origin:      leg.origin || firstSeg.origin || null,
      destination: leg.destination || lastSeg.destination || null,
      leg_type:    leg.leg_type || null,
    };
  });

  return { carrierMap, legMap };
}

// ── Leg entry builder ─────────────────────────────────────────────────────────

function buildLegEntry(legHash, lookups) {
  const { carrierMap, legMap } = lookups;
  const leg = legMap[legHash];

  if (!leg) {
    return {
      airline: "Unknown", logo: null,
      departure: null, arrival: null,
      origin: null, destination: null,
      duration: null, stops: "Non Stop",
    };
  }

  const carrier = carrierMap[leg.carrier] || {};

  return {
    airline:     carrier.name || leg.carrier || "Unknown",
    logo:        carrier.logo || null,
    departure:   leg.departure,
    arrival:     leg.arrival,
    origin:      leg.origin,
    destination: leg.destination,
    duration:    leg.duration,
    stops:       leg.stops,
  };
}

// ── Main formatFlightData ─────────────────────────────────────────────────────

/**
 * Converts raw GoZayaan API response to our canonical flight shape:
 *
 *   {
 *     departure:      { airline, logo, departure, arrival, origin, destination, duration, stops },
 *     return:         null | { … same … },
 *     totalPrice:     number,
 *     discountedPrice: number | null,
 *     currency:       string,
 *     fare_id:        string,   ← for booking deep-link
 *   }
 */
function formatFlightData(raw, params = {}) {
  const defaultResult = {
    flights: [],
    search_id: null,
    isCompleted: false,
  };

  if (!raw) return defaultResult;

  const result = raw.result || raw.data || raw;
  if (!result) return defaultResult;

  const lookups    = buildLookups(result);
  const isRoundTrip = !!params.returnDate;
  const searchId   = result.search_id || null;
  const isCompleted = !!result.isCompleted;

  const flights = (result.fares || []).reduce((acc, fare) => {
    const [depHash, retHash] = fare.leg_hashes || [];

    // Must have a departure leg hash
    if (!depHash) return acc;

    const departure = buildLegEntry(depHash, lookups);

    // For round trips require both legs
    let returnLeg = null;
    if (isRoundTrip) {
      if (!retHash || !lookups.legMap[retHash]) return acc; // incomplete pair
      returnLeg = buildLegEntry(retHash, lookups);
    }

    // GoZayaan pricing: total_fare_amount is the full fare.
    // discounted_fare_amount is the price after any applied discount (may not always exist).
    const totalPrice      = fare.total_fare_amount || 0;
    const discountedPrice = fare.discounted_fare_amount || fare.total_fare_amount || totalPrice;

    acc.push({
      departure,
      return:          returnLeg,
      totalPrice,
      discountedPrice,
      currency:        fare.currency || "BDT",
      fare_id:         fare.id || null,
    });

    return acc;
  }, []);

  flights.sort((a, b) => a.totalPrice - b.totalPrice);

  return { flights, search_id: searchId, isCompleted };
}

module.exports = { formatFlightData };
