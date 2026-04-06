const puppeteer = require("puppeteer");

const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";

function buildPageUrl({ from, to, date, returnDate, adult, child, infant, cabin_class }) {
  const tripType = returnDate ? "Return" : "OneWay";
  const cabin = cabin_class.charAt(0).toUpperCase() + cabin_class.slice(1).toLowerCase();
  
  let url = `https://sharetrip.net/flight-search?tripType=${tripType}&origin=${from}&destination=${to}&depart=${date}`;
  if (returnDate) url += `&depart=${returnDate}`;
  url += `&adult=${adult}&child=${child}&infant=${infant}&class=${cabin}&child2To5Count=0&child6To12Count=0&occupation=NOT_SELECTED`;
  return url;
}

async function scrapeFlights(params) {
  // PHASE 2: Direct Polling
  if (params.search_id) {
    console.log(`[ShareTrip] Direct Poll for ID: ${params.search_id}`);
    const allFlights = new Map();
    let latestResponse = null;
    let totalFlights = 0;
    const LIMIT = 10;

    try {
      // Fetch Page 1
      const res = await fetch(`https://api.sharetrip.net/api/v2/flight/search/available-flights?searchId=${params.search_id}`, {
        method: "POST",
        headers: { 
          "Content-Type": "application/json",
          "User-Agent": USER_AGENT,
          "Origin": "https://sharetrip.net",
          "Referer": "https://sharetrip.net/"
        },
        body: JSON.stringify({ page: 1, limit: LIMIT }),
      });
      const json = await res.json();
      latestResponse = json;
      const data = json.response;

      if (data) {
        (data.matchedFlights || []).forEach((f) => allFlights.set(f.sequenceCode, f));
        totalFlights = data.totalFlightsCount || 0;
        
        // If there are more pages, fetch them too to provide a complete "merged" result
        const totalPages = Math.ceil(totalFlights / LIMIT);
        if (totalPages > 1) {
          for (let pg = 2; pg <= totalPages; pg++) {
            try {
              const pRes = await fetch(`https://api.sharetrip.net/api/v2/flight/search/available-flights?searchId=${params.search_id}`, {
                method: "POST",
                headers: { 
                  "Content-Type": "application/json",
                  "User-Agent": USER_AGENT,
                  "Origin": "https://sharetrip.net",
                  "Referer": "https://sharetrip.net/"
                },
                body: JSON.stringify({ page: pg, limit: LIMIT }),
              });
              const pJson = await pRes.json();
              const pFlights = pJson.response?.matchedFlights || [];
              pFlights.forEach((f) => allFlights.set(f.sequenceCode, f));
            } catch (e) {
              console.error(`[ShareTrip] Error fetching page ${pg}: ${e.message}`);
            }
          }
        }
      }
    } catch (err) {
      console.error(`[ShareTrip] Direct fetch error: ${err.message}`);
    }

    // ShareTrip completion logic
    // We only mark it completed if we have some results and we've reached a stable point or high flight count
    const isCompleted = totalFlights > 0 && allFlights.size >= totalFlights && totalFlights > 5;

    return {
      response: {
        ...(latestResponse?.response || {}),
        matchedFlights: [...allFlights.values()],
        searchId: params.search_id,
        isCompleted: isCompleted,
      },
      code: latestResponse?.code,
      message: latestResponse?.message
    };
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
          }
        } catch (e) { }
      }
    });

    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 40000 });

    const start = Date.now();
    while (!capturedSearchId && Date.now() - start < 15000) {
      await new Promise((r) => setTimeout(r, 500));
    }

    // Try to get page 1 results quickly if we have ID
    let initialResults = { response: { searchId: capturedSearchId, matchedFlights: [], isCompleted: false } };
    if (capturedSearchId) {
       try {
         const quickFetch = await page.evaluate(async (sid) => {
            const res = await fetch(`https://api.sharetrip.net/api/v2/flight/search/available-flights?searchId=${sid}`, {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ page: 1, limit: 10 }),
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
