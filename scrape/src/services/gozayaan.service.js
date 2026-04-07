const puppeteer = require("puppeteer");

const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";

const API_HEADERS = {
  "Content-Type": "application/json",
  "User-Agent": USER_AGENT,
  "Accept": "application/json, text/plain, */*",
  "Origin": "https://gozayaan.com",
  "Referer": "https://gozayaan.com/",
};

// ── URL builder for browser navigation ────────────────────────────────────────
function buildUrl(params) {
  const { from, to, date, returnDate, adult, child, kids, infant, cabin_class } = params;
  const totalChildren = (child || 0) + (kids || 0);
  let trips = `${from},${to},${date}`;
  if (returnDate) trips += `,${to},${from},${returnDate}`;
  return `https://gozayaan.com/flight/list?adult=${adult}&child=${totalChildren}&child_age=&infant=${infant || 0}&cabin_class=${cabin_class || "Economy"}&trips=${trips}`;
}

// ── Phase 2: Direct API polling (no browser) ──────────────────────────────────
async function pollLegs(searchId, isRoundTrip) {
  const merged = {
    fares:    new Map(),
    legs:     new Map(),
    segments: new Map(),
    airports: new Map(),
    carriers: new Map(),
    status:   "PROCESSING",
    progress: 0,
    expected_progress: 0,
  };

  const pollTypes = isRoundTrip ? ["LA", "L1", "L2"] : ["LA", "L1"];

  for (const legType of pollTypes) {
    try {
      const resp = await fetch("https://production.gozayaan.com/api/flight/v4.0/search/legs/", {
        method: "POST",
        headers: API_HEADERS,
        body: JSON.stringify({ search_id: searchId, leg_type: legType }),
      });
      const json = await resp.json();
      const result = json.result;

      if (result) {
        (result.fares    || []).forEach((f) => merged.fares.set(f.id, f));
        (result.legs     || []).forEach((l) => merged.legs.set(l.hash, l));
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
      console.error(`[GoZayaan] Poll error (${legType}): ${err.message}`);
    }
  }

  const hasL2 = isRoundTrip
    ? [...merged.legs.values()].some((l) => l.leg_type === "L2")
    : true;
  const isCompleted = merged.status === "COMPLETED" && hasL2;

  return {
    result: {
      search_id:         searchId,
      fares:             [...merged.fares.values()],
      legs:              [...merged.legs.values()],
      segments:          [...merged.segments.values()],
      airports:          [...merged.airports.values()],
      carriers:          [...merged.carriers.values()],
      status:            merged.status,
      progress:          merged.progress,
      expected_progress: merged.expected_progress,
      isCompleted,
    },
  };
}

// ── Main scrapeFlights ────────────────────────────────────────────────────────
async function scrapeFlights(params) {
  const isRoundTrip = !!params.returnDate;

  // ── PHASE 2: Direct API poll using existing search_id ──────────────────────
  if (params.search_id) {
    console.log(`[GoZayaan] Phase 2 → Poll ID: ${params.search_id}`);
    return pollLegs(params.search_id, isRoundTrip);
  }

  // ── PHASE 1: Browser session to establish search + capture initial fares ────
  console.log(`[GoZayaan] Phase 1 → Browser init for ${params.from} → ${params.to}`);
  const url = buildUrl(params);

  const browser = await puppeteer.launch({
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"],
  });

  try {
    const page = await browser.newPage();
    await page.setUserAgent(USER_AGENT);

    const merged = {
      fares:    new Map(),
      legs:     new Map(),
      segments: new Map(),
      airports: new Map(),
      carriers: new Map(),
      status:   "PROCESSING",
      progress: 0,
      expected_progress: 0,
    };

    let capturedSearchId = null;

    // Intercept all GoZayaan API responses to collect fares & search_id
    page.on("response", async (response) => {
      const reqUrl = response.url();
      if (
        reqUrl.includes("/api/flight/") ||
        reqUrl.includes("/api/go_biz/flight/")
      ) {
        try {
          const ct = response.headers()["content-type"] || "";
          if (!ct.includes("application/json")) return;

          const json = await response.json();
          const result = json.result || json.data;
          if (!result) return;

          if (result.search_id) capturedSearchId = result.search_id;

          (result.fares    || []).forEach((f) => merged.fares.set(f.id, f));
          (result.legs     || []).forEach((l) => merged.legs.set(l.hash, l));
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
        } catch { /* ignore parse errors */ }
      }
    });

    await page.goto(url, { waitUntil: "networkidle2", timeout: 45000 });

    // Wait until search_id is found (up to 15 s)
    const deadline = Date.now() + 15000;
    while (!capturedSearchId && Date.now() < deadline) {
      await new Promise((r) => setTimeout(r, 500));
    }

    if (!capturedSearchId) {
      console.warn("[GoZayaan] Phase 1: could not capture search_id within 15s");
    } else {
      console.log(`[GoZayaan] Phase 1: search_id=${capturedSearchId}, fares so far=${merged.fares.size}`);
    }

    // Return Phase 1 results — controller will call Phase 2 polls for the rest
    return {
      result: {
        search_id:         capturedSearchId,
        fares:             [...merged.fares.values()],
        legs:              [...merged.legs.values()],
        segments:          [...merged.segments.values()],
        airports:          [...merged.airports.values()],
        carriers:          [...merged.carriers.values()],
        status:            merged.status,
        progress:          merged.progress,
        expected_progress: merged.expected_progress,
        isCompleted:       false, // always false so Phase 2 polling continues
      },
    };
  } finally {
    await browser.close();
  }
}

module.exports = { scrapeFlights };
