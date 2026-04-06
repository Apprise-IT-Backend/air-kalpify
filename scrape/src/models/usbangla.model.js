
function formatFlightData(raw, params = {}) {
  if (!raw || !raw.flights) return { flights: [], isCompleted: true };

  const isRoundTrip = !!params.returnDate;

  const flights = raw.flights.map((f) => {
    // Parse price to number
    let price = 0;
    if (f.price) {
      const priceStr = String(f.price).replace(/[^0-9.]/g, "");
      price = parseFloat(priceStr) || 0;
    }

    return {
      departure: {
        airline: "US-Bangla Airlines",
        logo: "https://fo-usba.ttinteractive.com/Zenith/FrontOffice/usbangla/images/logo.png",
        departure: f.departureTime,
        arrival: f.arrivalTime,
        duration: f.duration || "N/A",
        stops: f.stops || "Direct",
        origin: f.origin || params.from,
        destination: f.destination || params.to,
      },
      return: null, // Scraper currently only supports capturing departure leg in this simple way
      totalPrice: price,
      currency: "BDT",
    };
  });

  return {
    flights,
    search_id: raw.sessionPath || null,
    isCompleted: true,
  };
}

module.exports = { formatFlightData };
