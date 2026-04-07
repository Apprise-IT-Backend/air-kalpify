const puppeteer = require("puppeteer");

const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";
const SEARCH_TIMEOUT = 60000;
const POLL_INTERVAL = 2000;

function buildUrl(params) {
  const { from, to, date, returnDate, adult, child, kids, infant, cabin_class } = params;
  const totalChildren = (child || 0) + (kids || 0);
  const child_age = params.child_age || ""; // Optional, maybe we add individual ages later
  
  let trips = `${from},${to},${date}`;
  if (returnDate) {
    trips += `,${to},${from},${returnDate}`;
  }
  return `https://gozayaan.com/flight/list?adult=${adult}&child=${totalChildren}&child_age=${child_age}&infant=${infant}&cabin_class=${cabin_class}&trips=${trips}`;
}

async function scrapeFlights(params) {
  const isRoundTrip = !!params.returnDate;

  // PHASE 2: Direct Polling (Fast, no browser)
  if (params.search_id) {
    console.log(`[GoZayaan] Direct Poll for ID: ${params.search_id}`);
    const merged = {
      fares: new Map(),
      legs: new Map(),
      segments: new Map(),
      airports: new Map(),
      carriers: new Map(),
      status: "PROCESSING",
      progress: 0,
      expected_progress: 0,
    };

    const pollTypes = isRoundTrip ? ["LA", "L1", "L2"] : ["LA", "L1"];
    
    for (const legType of pollTypes) {
      try {
        const resp = await fetch("https://production.gozayaan.com/api/flight/v4.0/search/legs/", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ search_id: params.search_id, leg_type: legType }),
        });
        const json = await resp.json();
        const result = json.result;
        
        if (result) {
          (result.fares || []).forEach((f) => merged.fares.set(f.id, f));
          (result.legs || []).forEach((l) => merged.legs.set(l.hash, l));
          (result.segments || []).forEach((s) => merged.segments.set(s.hash, s));
          (result.airports || []).forEach((a) => merged.airports.set(a.code, a));
          (result.carriers || []).forEach((c) => merged.carriers.set(c.code, c));
          
          if (result.status) merged.status = result.status;
          if (typeof result.progress === "number") {
            merged.progress = Math.max(merged.progress, result.progress);
          }
          if (typeof result.expected_progress === "number" && result.expected_progress > 0) {
            merged.expected_progress = result.expected_progress;
          }
        }
      } catch (err) {
        console.error(`[GoZayaan] Direct fetch error: ${err.message}`);
      }
    }

    const hasL2 = isRoundTrip ? [...merged.legs.values()].some(l => l.leg_type === "L2") : true;
    const isCompleted = merged.status === "COMPLETED" && hasL2;

    return {
      result: {
        search_id: params.search_id,
        fares: [...merged.fares.values()],
        legs: [...merged.legs.values()],
        segments: [...merged.segments.values()],
        airports: [...merged.airports.values()],
        carriers: [...merged.carriers.values()],
        status: merged.status,
        progress: merged.progress,
        expected_progress: merged.expected_progress,
        isCompleted
      },
    };
  }

  // PHASE 1: Initialization (Launch browser to get search_id)
  const url = buildUrl(params);
  const browser = await puppeteer.launch({
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"],
  });

  try {
    const page = await browser.newPage();
    const merged = {
      fares: new Map(),
      legs: new Map(),
      segments: new Map(),
      airports: new Map(),
      carriers: new Map(),
      status: "PROCESSING",
      progress: 0,
      expected_progress: 0,
    };

    let capturedSearchId = null;

    page.on("response", async (response) => {
      const reqUrl = response.url();
      if (reqUrl.includes("/api/flight/") || reqUrl.includes("/api/go_biz/flight/")) {
        try {
          const contentType = response.headers()["content-type"] || "";
          if (contentType.includes("application/json")) {
            const json = await response.json();
            const result = json.result || json.data;
            if (!result) return;

            if (result.search_id) capturedSearchId = result.search_id;

            (result.fares || []).forEach((f) => merged.fares.set(f.id, f));
            (result.legs || []).forEach((l) => merged.legs.set(l.hash, l));
            (result.segments || []).forEach((s) => merged.segments.set(s.hash, s));
            (result.airports || []).forEach((a) => merged.airports.set(a.code, a));
            (result.carriers || []).forEach((c) => merged.carriers.set(c.code, c));
            
            if (result.status) merged.status = result.status;
            if (typeof result.progress === "number") merged.progress = result.progress;
            if (typeof result.expected_progress === "number") {
              merged.expected_progress = result.expected_progress;
            }
          }
        } catch { }
      }
    });

    await page.setUserAgent(USER_AGENT);
    await page.goto(url, { waitUntil: "networkidle2", timeout: 40000 });

    // Wait just enough to grab the ID
    const start = Date.now();
    while (!capturedSearchId && Date.now() - start < 15000) {
      await new Promise((r) => setTimeout(r, 500));
    }

    // Return Phase 1 results immediately
    return {
      result: {
        search_id: capturedSearchId,
        fares: [...merged.fares.values()],
        legs: [...merged.legs.values()],
        segments: [...merged.segments.values()],
        airports: [...merged.airports.values()],
        carriers: [...merged.carriers.values()],
        status: merged.status,
        progress: merged.progress,
        isCompleted: false
      },
    };
  } finally {
    await browser.close();
  }
}

module.exports = { scrapeFlights };
