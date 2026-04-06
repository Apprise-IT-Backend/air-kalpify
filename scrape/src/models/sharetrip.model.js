function formatTime(dt) {
  if (!dt) return null;
  // dt is { date: "2026-03-29", time: "19:35:00", timezone: "6" }
  if (typeof dt === "object" && dt.time) {
    return dt.time.substring(0, 5);
  }
  return null;
}

function formatDuration(minutes) {
  if (!minutes) return null;
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

function buildLegEntry(leg) {
  if (!leg) return null;
  const stops = (leg.stopCount || 0);
  return {
    airline: leg.airlines?.short || leg.airlines?.full || leg.marketingAirline,
    logo: leg.airlines?.logo || null,
    departure: formatTime(leg.departureDateTime),
    arrival: formatTime(leg.arrivalDateTime),
    origin: leg.origin?.code,
    destination: leg.destination?.code,
    duration: formatDuration(leg.duration),
    stops: stops === 0 ? "Non Stop" : `${stops} Stop${stops > 1 ? "s" : ""}`,
  };
}

function formatFlightData(raw, params = {}) {
  const defaultResult = { 
    flights: [], 
    search_id: raw?.response?.searchId || raw?.searchId || null, 
    isCompleted: !!(raw?.response?.isCompleted || raw?.isCompleted)
  };

  if (!raw || !raw.response) return defaultResult;

  const data = raw.response;
  const matchedFlights = data.matchedFlights || data.flights || [];
  const isRoundTrip = !!params.returnDate;

  const flights = matchedFlights.map((flight) => {
    const legs = flight.legs || [];
    const depLeg = legs[0];
    const retLeg = isRoundTrip ? legs[1] : null;

    const totalFare = flight.displayPrice?.totalFare || {};
    const promo = flight.promotionalCoupon;
    const currency = flight.currency || totalFare.currency || "BDT";

    return {
      departure: buildLegEntry(depLeg),
      return: retLeg ? buildLegEntry(retLeg) : null,
      totalPrice: totalFare.total || 0,
      discountedPrice: promo?.finalPriceAfterDiscount || null,
      coupon: promo?.couponCode || null,
      currency,
      sequenceCode: flight.sequenceCode, // Add sequenceCode for booking
    };
  });

  flights.sort((a, b) => a.totalPrice - b.totalPrice);
  return { 
    flights, 
    search_id: data.searchId || null, 
    isCompleted: !!data.isCompleted 
  };
}

module.exports = { formatFlightData };
