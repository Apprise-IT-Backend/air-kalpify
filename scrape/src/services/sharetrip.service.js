const puppeteer = require("puppeteer");

const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";
const SEARCH_TIMEOUT = 60000;
const POLL_INTERVAL = 2000;

function buildPageUrl({ from, to, date, returnDate, adult, child, infant, cabin_class }) {
  const tripType = returnDate ? "Return" : "OneWay";
  const cabin = cabin_class.charAt(0).toUpperCase() + cabin_class.slice(1).toLowerCase(); // e.g. Economy
  let url = `https://sharetrip.net/flight-search?tripType=${tripType}&origin=${from}&destination=${to}&depart=${date}`;
  if (returnDate) url += `&depart=${returnDate}`;
  url += `&adult=${adult}&child=${child}&infant=${infant}&class=${cabin}&child2To5Count=0&child6To12Count=0&occupation=NOT_SELECTED`;
  return url;
}

async function scrapeFlights(params) {
  const isRoundTrip = !!params.returnDate;

  // PHASE 2: Direct Polling (Wait, ShareTrip needs browser context for session)
  if (params.search_id) {
    console.log(`[ShareTrip] Browser-based Poll for ID: ${params.search_id}`);
    const browser = await puppeteer.launch({
      headless: true,
      args: ["--no-sandbox", "--disable-setuid-sandbox"],
    });

    try {
      const page = await browser.newPage();
      await page.setUserAgent(USER_AGENT);
      // Go to home page just to get the session/domain context
      await page.goto("https://sharetrip.net", { waitUntil: "domcontentloaded", timeout: 20000 });

      const result = await page.evaluate(async (sid) => {
        try {
          const res = await fetch(`https://api.sharetrip.net/api/v2/flight/search/${sid}`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ page: 1, limit: 50, sortBy: "CHEAPEST" }),
          });
          return await res.json();
        } catch (e) {
          return { error: e.message };
        }
      }, params.search_id);

      if (result && result.response) {
         // Determine if completed (ShareTrip doesn't always provide an isCompleted flag, 
         // so we rely on count vs results if available)
         const data = result.response;
         const total = data.totalFlightsCount || 0;
         const found = (data.matchedFlights || []).length;
         result.response.isCompleted = total > 0 && found >= total;
         result.response.searchId = params.search_id;
      }
      
      return result;
    } catch (err) {
      console.error(`[ShareTrip] Browser poll error: ${err.message}`);
      return { response: null, error: err.message };
    } finally {
      await browser.close();
    }
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
      if (reqUrl.includes("flight/search/initialize")) {
        let fixedUrl = reqUrl;
        let modified = false;

        // Ensure brackets for array fields
        ["departureDates", "origins", "destinations"].forEach(param => {
          if (fixedUrl.includes(`${param}=`) && !fixedUrl.includes(`${param}[]=`)) {
            fixedUrl = fixedUrl.replace(new RegExp(`${param}=`, "g"), `${param}[]=`);
            modified = true;
          }
        });

        // ShareTrip API expects ONEWAY (no underscore)
        if (fixedUrl.includes("tripType=ONE_WAY")) {
          fixedUrl = fixedUrl.replace("tripType=ONE_WAY", "tripType=ONEWAY");
          modified = true;
        }

        // Ensure numeric passenger counts are present
        if (!fixedUrl.includes("numOfKid=")) {
          fixedUrl += "&numOfKid=0";
          modified = true;
        }

        if (modified) {
          const encodedUrl = fixedUrl.replace(/\[\]/g, "%5B%5D");
          request.continue({ url: encodedUrl });
          return;
        }
      }
      request.continue();
    });

    page.on("response", async (response) => {
      const reqUrl = response.url();
      if (reqUrl.includes("api.sharetrip.net") && reqUrl.includes("/flight/search/")) {
        try {
          const contentType = response.headers()["content-type"] || "";
          if (!contentType.includes("application/json")) return;
          const json = await response.json();
          if (json.response?.searchId) {
            capturedSearchId = json.response.searchId;
          } else if (json.searchId) {
            capturedSearchId = json.searchId;
          }
        } catch (e) { }
      }
    });

    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 40000 });

    const start = Date.now();
    while (!capturedSearchId && Date.now() - start < 20000) {
      await new Promise((r) => setTimeout(r, 500));
    }

    // Try to get page 1 results quickly if we have ID
    let initialResults = { response: { searchId: capturedSearchId, matchedFlights: [], isCompleted: false } };
    if (capturedSearchId) {
       // Wait a bit for backend to process
       await new Promise((r) => setTimeout(r, 4000));
       try {
          const quickFetch = await page.evaluate(async (sid) => {
             const res = await fetch(`https://api.sharetrip.net/api/v2/flight/search/${sid}`, {
               method: "POST",
               headers: { "Content-Type": "application/json" },
               body: JSON.stringify({ 
                 page: 1, 
                 limit: 50, 
                 sortBy: "CHEAPEST" 
               }),
             });
             return res.json();
          }, capturedSearchId);

         if (quickFetch?.response) {
            initialResults = quickFetch;
            const data = quickFetch.response;
            const total = data.totalFlightsCount || 0;
            const found = (data.matchedFlights || []).length;
            
            initialResults.response.searchId = capturedSearchId;
            initialResults.response.isCompleted = total > 0 && found >= total;
         }
       } catch { }
    }

    return initialResults;
  } finally {
    await browser.close();
  }
}

module.exports = { scrapeFlights };
