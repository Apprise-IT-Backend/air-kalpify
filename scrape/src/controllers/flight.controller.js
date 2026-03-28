const gozayaanService = require("../services/gozayaan.service");
const sharetripService = require("../services/sharetrip.service");
const gozayaanModel = require("../models/gozayaan.model");
const sharetripModel = require("../models/sharetrip.model");

const providers = {
  gozayaan: {
    scrape: gozayaanService.scrapeFlights,
    format: gozayaanModel.formatFlightData,
  },
  sharetrip: {
    scrape: sharetripService.scrapeFlights,
    format: sharetripModel.formatFlightData,
  },
};

function parseParams(query) {
  return {
    from: query.from || "DAC",
    to: query.to || "CXB",
    date: query.date || "2026-03-27",
    returnDate: query.returnDate || null,
    adult: query.adult || 1,
    child: query.child || 0,
    child_age: query.child_age || "",
    infant: query.infant || 0,
    cabin_class: query.cabin_class || "Economy",
  };
}

async function scrapeProvider(name, params) {
  const provider = providers[name];
  if (!provider) return null;

  const tripType = params.returnDate ? "Round Trip" : "One Way";
  console.log(`[${name}] Scraping ${tripType} flights...`);

  const raw = await provider.scrape(params);
  const data = provider.format(raw, params);
  return { provider: name, flights: data.flights || [] };
}

async function getFlights(req, res) {
  try {
    const params = parseParams(req.query);
    const tripType = params.returnDate ? "Round Trip" : "One Way";
    const requestedProvider = req.query.provider?.toLowerCase();

    if (requestedProvider) {
      if (!providers[requestedProvider]) {
        return res.status(400).json({
          success: false,
          error: `Unknown provider: ${requestedProvider}. Available: ${Object.keys(providers).join(", ")}`,
        });
      }

      const result = await scrapeProvider(requestedProvider, params);
      return res.json({ success: true, tripType, ...result });
    }

    // No provider specified — scrape all in parallel and merge
    const results = await Promise.allSettled(
      Object.keys(providers).map((name) => scrapeProvider(name, params))
    );

    const merged = [];
    const providerList = [];

    results.forEach((result) => {
      if (result.status === "fulfilled" && result.value) {
        const { provider, flights } = result.value;
        providerList.push(provider);
        flights.forEach((f) => merged.push({ ...f, provider }));
      }
    });

    // Sort by totalPrice
    merged.sort((a, b) => a.totalPrice - b.totalPrice);

    return res.json({
      success: true,
      tripType,
      providers: providerList,
      flights: merged,
    });
  } catch (err) {
    console.error("Scrape error:", err.message);
    res.status(500).json({ success: false, error: err.message });
  }
}

function healthCheck(req, res) {
  res.json({
    message: "Flight Scraper API",
    usage: {
      singleProvider: "GET /api/flights?from=DAC&to=CXB&date=2026-03-28&provider=gozayaan",
      allProviders: "GET /api/flights?from=DAC&to=CXB&date=2026-03-28&returnDate=2026-03-29",
    },
    availableProviders: Object.keys(providers),
    params: {
      from: "Origin airport code (default: DAC)",
      to: "Destination airport code (default: CXB)",
      date: "Departure date YYYY-MM-DD",
      returnDate: "Return date YYYY-MM-DD (optional, makes it round trip)",
      adult: "Number of adults (default: 1)",
      child: "Number of children (default: 0)",
      infant: "Number of infants (default: 0)",
      cabin_class: "Economy | Business | First (default: Economy)",
      provider: "gozayaan | sharetrip (optional, omit to query all)",
    },
  });
}

module.exports = { getFlights, healthCheck };
