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
  const url = buildPageUrl(params);

  const browser = await puppeteer.launch({
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"],
  });

  try {
    const page = await browser.newPage();
    await page.setUserAgent(USER_AGENT);

    let searchId = null;

    // Intercept requests to fix departureDates array param
    await page.setRequestInterception(true);
    page.on("request", (request) => {
      const reqUrl = request.url();
      if (reqUrl.includes("flight/search/initialize") && reqUrl.includes("departureDates=") && !reqUrl.includes("departureDates[]=")) {
        let fixedUrl = reqUrl.replace(/departureDates=/g, "departureDates[]=");
        fixedUrl = fixedUrl.replace("tripType=ONE_WAY", "tripType=ONEWAY");
        if (!fixedUrl.includes("origins[]=")) {
          fixedUrl = fixedUrl.replace(/origins=/g, "origins[]=");
        }
        request.continue({ url: fixedUrl });
      } else {
        request.continue();
      }
    });

    // Capture searchId from initialize response
    page.on("response", async (response) => {
      const reqUrl = response.url();
      if (!reqUrl.includes("api.sharetrip.net") || !reqUrl.includes("/flight/search/")) return;
      try {
        const contentType = response.headers()["content-type"] || "";
        if (!contentType.includes("application/json")) return;
        const json = await response.json();
        if (json.response?.searchId && !searchId) {
          searchId = json.response.searchId;
          console.log(`[ShareTrip] Got searchId: ${searchId}`);
        }
      } catch {
        // ignore
      }
    });

    console.log(`[ShareTrip] Scraping: ${url}`);
    try {
      await page.goto(url, { waitUntil: "domcontentloaded", timeout: 20000 });
    } catch {
      // OK
    }

    // Wait for searchId
    const idDeadline = Date.now() + 15000;
    while (!searchId && Date.now() < idDeadline) {
      await new Promise((r) => setTimeout(r, 500));
    }

    if (!searchId) {
      console.log(`[ShareTrip] No searchId captured.`);
      return { response: null };
    }

    // Fetch all pages of results
    const allFlights = new Map();
    let latestResponse = null;
    const LIMIT = 10;
    let currentPage = 1;
    let totalFlights = 0;

    const fetchPage = async (pageNum) => {
      return page.evaluate(async (sid, pg, lim) => {
        const res = await fetch(`https://api.sharetrip.net/api/v2/flight/search/${sid}`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ page: pg, limit: lim, sortBy: "CHEAPEST" }),
        });
        return res.json();
      }, searchId, pageNum, LIMIT);
    };

    // Brief wait for search to start processing
    await new Promise((r) => setTimeout(r, 2000));

    // Poll page 1 until we get results
    const deadline = Date.now() + SEARCH_TIMEOUT;
    while (Date.now() < deadline) {
      await new Promise((r) => setTimeout(r, POLL_INTERVAL));

      try {
        const result = await fetchPage(1);
        const data = result.response;
        if (!data) continue;

        const flights = data.matchedFlights || [];
        totalFlights = data.totalFlightsCount || 0;

        if (flights.length > 0) {
          flights.forEach((f) => allFlights.set(f.sequenceCode, f));
          latestResponse = result;
          console.log(`[ShareTrip] Page 1: ${flights.length} flights, total: ${totalFlights}`);
          break;
        }
      } catch (err) {
        console.log(`[ShareTrip] Poll error: ${err.message}`);
      }
    }

    // Keep polling until totalFlightsCount stabilizes, fetching all pages each time
    let lastTotal = totalFlights;
    let stableRounds = 0;

    while (Date.now() < deadline) {
      // Fetch all remaining pages for current total
      if (totalFlights > allFlights.size) {
        const totalPages = Math.ceil(totalFlights / LIMIT);
        for (let pg = 2; pg <= totalPages; pg++) {
          try {
            await new Promise((r) => setTimeout(r, 500));
            const result = await fetchPage(pg);
            const flights = result.response?.matchedFlights || [];
            flights.forEach((f) => allFlights.set(f.sequenceCode, f));
            if (flights.length > 0) console.log(`[ShareTrip] Page ${pg}: ${flights.length} flights`);
          } catch {
            // ignore
          }
        }
      }

      // Wait and re-check page 1 to see if totalFlightsCount increased
      await new Promise((r) => setTimeout(r, 5000));

      try {
        const result = await fetchPage(1);
        const data = result.response;
        if (!data) continue;

        const flights = data.matchedFlights || [];
        flights.forEach((f) => allFlights.set(f.sequenceCode, f));
        totalFlights = data.totalFlightsCount || totalFlights;
        latestResponse = result;

        console.log(`[ShareTrip] Re-poll: total=${totalFlights}, captured=${allFlights.size}`);

        if (totalFlights === lastTotal) {
          stableRounds++;
          if (stableRounds >= 3) break;
        } else {
          stableRounds = 0;
          lastTotal = totalFlights;
        }
      } catch {
        break;
      }
    }

    console.log(`[ShareTrip] Done. ${allFlights.size} flights captured.`);

    if (!latestResponse) return { response: null };

    // Replace matchedFlights with full accumulated set
    latestResponse.response.matchedFlights = [...allFlights.values()];
    return latestResponse;
  } finally {
    await browser.close();
  }
}

module.exports = { scrapeFlights };
