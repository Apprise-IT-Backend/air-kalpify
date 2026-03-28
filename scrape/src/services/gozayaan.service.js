const puppeteer = require("puppeteer");

const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";
const SEARCH_TIMEOUT = 60000;
const POLL_INTERVAL = 2000;

function buildUrl({ from, to, date, returnDate, adult, child, child_age, infant, cabin_class }) {
  let trips = `${from},${to},${date}`;
  if (returnDate) {
    trips += `,${to},${from},${returnDate}`;
  }
  return `https://gozayaan.com/flight/list?adult=${adult}&child=${child}&child_age=${child_age}&infant=${infant}&cabin_class=${cabin_class}&trips=${trips}`;
}

async function scrapeFlights(params) {
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
    let searchDone = false;

    page.on("response", async (response) => {
      const reqUrl = response.url();
      if (reqUrl.includes("/api/flight/") || reqUrl.includes("/api/go_biz/flight/")) {
        try {
          const contentType = response.headers()["content-type"] || "";
          if (contentType.includes("application/json")) {
            const json = await response.json();
            const result = json.result || json.data;
            if (!result) return;

            (result.fares || []).forEach((f) => merged.fares.set(f.id, f));
            (result.legs || []).forEach((l) => merged.legs.set(l.hash, l));
            (result.segments || []).forEach((s) => merged.segments.set(s.hash, s));
            (result.airports || []).forEach((a) => merged.airports.set(a.code, a));
            (result.carriers || []).forEach((c) => merged.carriers.set(c.code, c));

            if (result.status) merged.status = result.status;
            if (typeof result.progress === "number") merged.progress = result.progress;
            if (typeof result.expected_progress === "number" && result.expected_progress > 0) {
              merged.expected_progress = result.expected_progress;
            }

            const hasData = merged.fares.size > 0;
            const progressDone = merged.expected_progress > 0 && merged.progress >= merged.expected_progress;
            const statusDone = merged.status === "COMPLETED";

            if (hasData && (progressDone || statusDone)) {
              searchDone = true;
            }
          }
        } catch {
          // ignore non-JSON responses
        }
      }
    });

    await page.setUserAgent(USER_AGENT);
    console.log(`[GoZayaan] Scraping: ${url}`);
    await page.goto(url, { waitUntil: "networkidle0", timeout: 60000 });

    const deadline = Date.now() + SEARCH_TIMEOUT;
    while (!searchDone && Date.now() < deadline) {
      await new Promise((r) => setTimeout(r, POLL_INTERVAL));
    }

    console.log(`[GoZayaan] Done. ${merged.fares.size} fares captured.`);
    return {
      result: {
        fares: [...merged.fares.values()],
        legs: [...merged.legs.values()],
        segments: [...merged.segments.values()],
        airports: [...merged.airports.values()],
        carriers: [...merged.carriers.values()],
        status: merged.status,
        progress: merged.progress,
        expected_progress: merged.expected_progress,
      },
    };
  } finally {
    await browser.close();
  }
}

module.exports = { scrapeFlights };
