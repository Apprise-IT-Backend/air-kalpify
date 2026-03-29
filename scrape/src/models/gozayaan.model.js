function formatDuration(minutes) {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

function formatTime(dateTime) {
  if (!dateTime) return null;
  return dateTime.split("T")[1]?.substring(0, 5) || null;
}

function buildLookups(result) {
  const carrierMap = {};
  (result.carriers || []).forEach((c) => {
    carrierMap[c.code] = { name: c.name, logo: c.logo };
  });

  // Build segment map first so we can resolve leg origin/destination
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

    // Resolve origin from first segment, destination from last segment
    const firstSeg = segmentMap[segHashes[0]] || {};
    const lastSeg = segmentMap[segHashes[segHashes.length - 1]] || {};

    legMap[leg.hash] = {
      departure: formatTime(leg.departure_date_time),
      arrival: formatTime(leg.arrival_date_time),
      duration: formatDuration(leg.travel_time),
      stops: stops === 0 ? "Non Stop" : `${stops} Stop${stops > 1 ? "s" : ""}`,
      carrier: leg.marketing_carrier,
      origin: leg.origin || firstSeg.origin,
      destination: leg.destination || lastSeg.destination,
      segmentHashes: segHashes,
    };
  });

  return { carrierMap, legMap, segmentMap };
}

function buildLegEntry(legHash, lookups) {
  const { carrierMap, legMap } = lookups;
  const leg = legMap[legHash] || null;

  if (!leg) {
    return {
      airline: "Unknown",
      logo: null,
      departure: null,
      arrival: null,
      origin: null,
      destination: null,
      duration: "Unknown",
      stops: "Unknown",
    };
  }

  const carrier = carrierMap[leg.carrier] || {};

  return {
    airline: carrier.name || leg.carrier || "Unknown",
    logo: carrier.logo || null,
    departure: leg.departure || null,
    arrival: leg.arrival || null,
    origin: leg.origin || null,
    destination: leg.destination || null,
    duration: leg.duration || "Unknown",
    stops: leg.stops || "Non Stop",
  };
}

function formatFlightData(raw, params = {}) {
  if (!raw) return null;

  const result = raw.result || raw.data || raw;
  const lookups = buildLookups(result);
  const isRoundTrip = !!params.returnDate;

  const flights = (result.fares || []).reduce((acc, fare) => {
    const [depHash, retHash] = fare.leg_hashes || [];
    
    // Skip if missing departure hash
    if (!depHash) return acc;

    const departure = buildLegEntry(depHash, lookups);
    
    // For round trips, validate return leg exists in data
    let returnLeg = null;
    if (isRoundTrip) {
      if (!retHash || !lookups.legMap[retHash]) {
        // Skip incomplete round trip result
        return acc;
      }
      returnLeg = buildLegEntry(retHash, lookups);
    }

    acc.push({
      departure,
      return: returnLeg,
      totalPrice: fare.total_fare_amount,
      currency: fare.currency,
      fare_id: fare.id, // Include fare_id for booking
    });

    return acc;
  }, []);

  // Sort by total price
  flights.sort((a, b) => a.totalPrice - b.totalPrice);

  return { 
    flights, 
    search_id: result.search_id || null, 
    isCompleted: !!result.isCompleted 
  };
}

module.exports = { formatFlightData };
