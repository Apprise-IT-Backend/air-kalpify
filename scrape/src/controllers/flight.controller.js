const gozayaanService = require("../services/gozayaan.service");
const sharetripService = require("../services/sharetrip.service");
const gozayaanModel = require("../models/gozayaan.model");
const sharetripModel = require("../models/sharetrip.model");
const db = require("../utils/db");

const providers = {
  // gozayaan: {
  //   scrape: gozayaanService.scrapeFlights,
  //   format: gozayaanModel.formatFlightData,
  // },
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
    adult: parseInt(query.adults || query.adult || 1),
    child: parseInt(query.children || query.child || 0),
    kids: parseInt(query.kids || 0),
    infant: parseInt(query.infants || query.infant || 0),
    cabin_class: query.cabin_class || "Economy",
    search_id: query.search_id || null,
  };
}

async function scrapeProvider(name, params) {
  //console.log("Scraping provider:", name);
  const provider = providers[name];
  if (!provider) return null;

  const tripType = params.returnDate ? "Round Trip" : "One Way";
  //console.log(`[${name}] Scraping ${tripType} flights (ID: ${params.search_id || 'NEW'})...`);

  let data = null;
  let currentSearchId = params.search_id;
  let attempts = 0;
  const MAX_ATTEMPTS = 15; // Max 30-45 seconds of polling

  // Initial scrape (Phase 1)
  const raw = await provider.scrape(params);
    console.log("Data:", raw);
  data = provider.format(raw, params);
  
  // If not completed and we have a search_id, poll until done (for GoZayaan/ShareTrip)
  console.log("Data:", data);
  if (!data.isCompleted && data.search_id) {
    currentSearchId = data.search_id;
    console.log(`[${name}] Polling until completed (ID: ${currentSearchId})...`);
    
    while (!data.isCompleted && attempts < MAX_ATTEMPTS) {
      attempts++;
      await new Promise(r => setTimeout(r, 3000)); // Poll every 3s
      
      const pollRaw = await provider.scrape({ ...params, search_id: currentSearchId });
      const pollData = provider.format(pollRaw, params);
      
      // Update data with new flights and status
      if (pollData.flights && pollData.flights.length > 0) {
          data.flights = pollData.flights;
      }
      data.isCompleted = pollData.isCompleted;
      console.log(`[${name}] Poll attempt ${attempts}: Found ${data.flights.length} flights (Completed: ${data.isCompleted})`);
    }
  }
  
  return { 
    provider: name, 
    flights: data.flights || [],
    search_id: data.search_id,
    isCompleted: data.isCompleted
  };
}

async function saveToDb(params, provider, flights, searchId) {
  try {
    const tripType = params.returnDate ? "round-way" : "one-way";
    // For DB storage, we combine children and kids if schema only has 'children'
    const totalChildren = (params.child || 0) + (params.kids || 0);
    
    // Check if record exists for this EXACT search criteria
    const [rows] = await db.execute(
      `SELECT id FROM flights WHERE from_location = ? AND to_location = ? AND departure_date = ? AND provider = ? AND adults = ? AND children = ? AND infants = ? AND cabin_class = ?`,
      [params.from, params.to, params.date, provider, params.adult, totalChildren, params.infant, params.cabin_class]
    );

    const resultsJson = JSON.stringify(flights);

    if (rows.length > 0) {
      // Update existing record
      await db.execute(
        `UPDATE flights SET results = ?, search_id = ?, search_at = NOW(), updated_at = NOW() WHERE id = ?`,
        [resultsJson, searchId || null, rows[0].id]
      );
    } else {
      // Create new record
      await db.execute(
        `INSERT INTO flights (from_location, to_location, departure_date, return_date, adults, children, infants, cabin_class, trip_type, provider, results, search_id, search_at, created_at, updated_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())`,
        [params.from, params.to, params.date, params.returnDate, params.adult, totalChildren, params.infant, params.cabin_class, tripType, provider, resultsJson, searchId || null]
      );
    }
    console.log(`[MySQL] Saved ${flights.length} flights for ${provider} (ID: ${searchId})`);
  } catch (err) {
    console.error("[MySQL] Error saving flights:", err.message);
  }
}

async function getFlights(req, res) {
  try {
    console.log("Query:", req.query);
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
      
      // Save to MySQL (Wait for it to ensure Laravel finds it in DB right after)
      if (result && result.flights.length > 0) {
        await saveToDb(params, requestedProvider, result.flights, result.search_id).catch(err => console.error(err));
      }
      
      return res.json({ success: true, tripType, ...result });
    }

    // No provider specified — scrape all in parallel and merge
    console.log(`[Controller] Starting parallel scrape for all providers...`);
    const providerNames = Object.keys(providers);
    const results = await Promise.allSettled(
      providerNames.map((name) => {
        // Deep clone params to avoid any shared state issues
        const providerParams = JSON.parse(JSON.stringify(params));
        return scrapeProvider(name, providerParams);
      })
    );

    const merged = [];
    const providerList = [];

    results.forEach((result) => {
      if (result.status === "fulfilled" && result.value) {
        const { provider, flights, search_id } = result.value;
        providerList.push(provider);
        flights.forEach((f) => merged.push({ ...f, provider }));
        
        // Save each provider results to DB independently
        if (flights.length > 0) {
           saveToDb(params, provider, flights, search_id).catch(err => console.error(`[MySQL] Save error for ${provider}: ${err.message}`));
        }
      } else if (result.status === "rejected") {
        console.error(`[Controller] Provider failed:`, result.reason);
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
