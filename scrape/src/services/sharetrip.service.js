const puppeteer = require("puppeteer");

const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";
const SEARCH_TIMEOUT = 60000;
const POLL_INTERVAL = 2000;

function buildPageUrl({ from, to, date, returnDate, adult, child, infant, cabin_class }) {
  const tripType = returnDate ? "Return" : "OneWay";
  let url = `https://sharetrip.net/flight-search?adult=${adult}&child=${child}&child2To5Count=0&child6To12Count=0&class=${cabin_class}&depart=${date}`;
  if (returnDate) url += `&depart=${returnDate}`;
  url += `&destination=${to}&infant=${infant}&occupation=NOT_SELECTED&origin=${from}&tripType=${tripType}`;
  return url;
}

async function scrapeFlights(params) {
  const isRoundTrip = !!params.returnDate;

  // PHASE 2: Direct Polling (Fast, no browser)
  if (params.search_id) {
    console.log(`[ShareTrip] Direct Poll for ID: ${params.search_id}`);
    const allFlights = new Map();
    let latestResponse = null;
    let totalFlights = 0;
    const LIMIT = 50; // Use a larger limit for direct polling

    try {
      const res = await fetch(`https://api.sharetrip.net/api/v2/flight/search/${params.search_id}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ page: 1, limit: LIMIT, sortBy: "CHEAPEST" }),
      });
      const json = await res.json();
      latestResponse = json;
      const data = json.response;

      if (data) {
        (data.matchedFlights || []).forEach((f) => allFlights.set(f.sequenceCode, f));
        totalFlights = data.totalFlightsCount || 0;
      }
    } catch (err) {
      console.error(`[ShareTrip] Direct fetch error: ${err.message}`);
    }

    const isCompleted = latestResponse?.response && allFlights.size >= totalFlights && totalFlights > 0;

    if (latestResponse && latestResponse.response) {
      latestResponse.response.matchedFlights = [...allFlights.values()];
      latestResponse.response.searchId = params.search_id;
      latestResponse.response.isCompleted = isCompleted;
    }

    return latestResponse;
  }

  // PHASE 1: Initialization
  const url = buildPageUrl(params);
  const browser = await puppeteer.launch({
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"],
  });

  try {
    const page = await browser.newPage();
    await page.setUserAgent(USER_AGENT);
    let capturedSearchId = null;

    // Intercept requests to fix params
    await page.setRequestInterception(true);
    page.on("request", (request) => {
      const reqUrl = request.url();
      if (reqUrl.includes("flight/search/initialize") && reqUrl.includes("departureDates=") && !reqUrl.includes("departureDates[]=")) {
        let fixedUrl = reqUrl.replace(/departureDates=/g, "departureDates[]=");
        fixedUrl = fixedUrl.replace("tripType=ONE_WAY", "tripType=ONEWAY");
        if (!fixedUrl.includes("origins[]=")) fixedUrl = fixedUrl.replace(/origins=/g, "origins[]=");
        request.continue({ url: fixedUrl });
      } else {
        request.continue();
      }
    });

    page.on("response", async (response) => {
      const reqUrl = response.url();
      if (!reqUrl.includes("api.sharetrip.net") || !reqUrl.includes("/flight/search/")) return;
      try {
        const contentType = response.headers()["content-type"] || "";
        if (!contentType.includes("application/json")) return;
        const json = await response.json();
        if (json.response?.searchId) capturedSearchId = json.response.searchId;
      } catch { }
    });

    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 25000 });

    const start = Date.now();
    while (!capturedSearchId && Date.now() - start < 15000) {
      await new Promise((r) => setTimeout(r, 500));
    }

    // Try to get page 1 results quickly if we have ID
    let initialResults = { response: { searchId: capturedSearchId, matchedFlights: [], isCompleted: false } };
    if (capturedSearchId) {
       try {
         const quickFetch = await page.evaluate(async (sid) => {
            const res = await fetch(`https://api.sharetrip.net/api/v2/flight/search/${sid}`, {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ page: 1, limit: 10, sortBy: "CHEAPEST" }),
            });
            return res.json();
         }, capturedSearchId);
         if (quickFetch?.response) {
            initialResults = quickFetch;
            initialResults.response.searchId = capturedSearchId;
            initialResults.response.isCompleted = false;
         }
       } catch { }
    }

    return initialResults;
  } finally {
    await browser.close();
  }
}

module.exports = { scrapeFlights };
