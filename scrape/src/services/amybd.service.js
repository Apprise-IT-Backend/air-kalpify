const puppeteer = require("puppeteer");

// ── Anti-Blocking Utilities ─────────────────────────────────────────────────

const USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:132.0) Gecko/20100101 Firefox/132.0",
    "Mozilla/5.0 (AppleChromebook; OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36"
];

const getRandomUserAgent = () => USER_AGENTS[Math.floor(Math.random() * USER_AGENTS.length)];

const getRandomIp = () => `${Math.floor(Math.random() * 255) + 1}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}`;

const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
const randomDelay = (min = 500, max = 2000) => sleep(Math.floor(Math.random() * (max - min + 1) + min));

// ── Constants ─────────────────────────────────────────────────────────────────

const HOME_URL = "https://www.amybd.com/flights";
const SEARCH_URL = "https://www.amybd.com/atapi.aspx";

// ── IATA → AmyBD city-string map ──────────────────────────────────────────────
const IATA_TO_AMY = {
    DAC: "Dhaka - DAC - BANGLADESH",
    CXB: "Coxs Bazar - CXB - BANGLADESH",
    CGP: "Chittagong - CGP - BANGLADESH",
    ZYL: "Sylhet - ZYL - BANGLADESH",
    JSR: "Jessore - JSR - BANGLADESH",
    BZL: "Barisal - BZL - BANGLADESH",
    RJH: "Rajshahi - RJH - BANGLADESH",
    SPD: "Saidpur - SPD - BANGLADESH",
    DXB: "Dubai - DXB - UNITED ARAB EMIRATES",
    AUH: "Abu Dhabi - AUH - UNITED ARAB EMIRATES",
    DOH: "Doha - DOH - QATAR",
    RUH: "Riyadh - RUH - SAUDI ARABIA",
    JED: "Jeddah - JED - SAUDI ARABIA",
    MCT: "Muscat - MCT - OMAN",
    KWI: "Kuwait City - KWI - KUWAIT",
    BAH: "Bahrain - BAH - BAHRAIN",
    DEL: "Delhi - DEL - INDIA",
    BOM: "Mumbai - BOM - INDIA",
    MAA: "Chennai - MAA - INDIA",
    CCU: "Kolkata - CCU - INDIA",
    KTM: "Kathmandu - KTM - NEPAL",
    CMB: "Colombo - CMB - SRI LANKA",
    MLE: "Male - MLE - MALDIVES",
    SIN: "Singapore - SIN - SINGAPORE",
    KUL: "Kuala Lumpur - KUL - MALAYSIA",
    BKK: "Bangkok - BKK - THAILAND",
    HKG: "Hong Kong - HKG - HONG KONG",
    LHR: "London - LHR - UNITED KINGDOM",
    CDG: "Paris - CDG - FRANCE",
    FRA: "Frankfurt - FRA - GERMANY",
    JFK: "New York - JFK - USA",
};

/**
 * Convert a bare IATA code into the AmyBD city string.
 */
function toAmyCity(iata) {
    const code = (iata || "").toUpperCase().trim();
    return IATA_TO_AMY[code] || `Unknown - ${code} - UNKNOWN`;
}

// ── Date helpers ──────────────────────────────────────────────────────────────

const MONTHS = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

/**
 * Convert YYYY-MM-DD → DD-Mon-YYYY (format AmyBD expects)
 */
function toAmyDate(iso) {
    if (!iso) return null;
    const [y, m, d] = iso.split("-");
    const date = new Date(y, parseInt(m, 10) - 1, d);
    if (isNaN(date.getTime())) return null;
    return `${String(d).padStart(2, '0')}-${MONTHS[parseInt(m, 10) - 1]}-${y}`;
}


/**
 * @param {object} params 
 * @returns {object}
 */
async function scrapeFlights(params) {
    const {
        from,
        to,
        date,
        returnDate,
        adult = 1,
        child = 0,
        kids = 0,
        infant = 0,
        cabin_class = "Economy",
    } = params;

    const totalChildren = (child || 0) + (kids || 0);
    const trip = returnDate ? "RT" : "OW";
    const depDate = toAmyDate(date);
    const retDate = returnDate ? toAmyDate(returnDate) : toAmyDate(date); // Amy requires a value

    const cabin = cabin_class === "Economy" ? "Y" : "C";
    const fromCity = toAmyCity(from);
    const toCity = toAmyCity(to);

    console.log(`[AmyBD] Initializing Search: ${trip} ${fromCity} -> ${toCity}`);

    const userAgent = getRandomUserAgent();
    const proxyIp = getRandomIp();

    const browser = await puppeteer.launch({
        headless: true,
        args: [
            "--no-sandbox", 
            "--disable-setuid-sandbox",
            `--user-agent=${userAgent}`
        ],
    });

    try {
        const page = await browser.newPage();
        
        // Randomize headers to prevent IP/Bot detection
        await page.setExtraHTTPHeaders({
            "X-Forwarded-For": proxyIp,
            "X-Real-IP": proxyIp,
            "Client-IP": proxyIp
        });

        console.log("[AmyBD] Navigating to homepage to get session/token...");
        await page.goto(HOME_URL, { waitUntil: "networkidle2", timeout: 45000 });

        // Small human-like delay
        await randomDelay(1000, 3000);

        // Get fresh token from page context
        const token = await page.evaluate(async () => {
            if (typeof window.FLTkn === 'function') {
                return window.FLTkn();
            }
            return window.amyTK || window.TOKEN || window.gTOKEN || null;
        });

        if (!token) {
            throw new Error("Could not acquire token from AmyBD homepage");
        }

        console.log(`[AmyBD] Token acquired: ${token.substring(0, 10)}... (IP: ${proxyIp})`);

        const payload = {
            is_combo: 0,
            CMND: "_FLIGHTSEARCHOPEN_",
            TRIP: trip,
            FROM: fromCity,
            DEST: toCity,
            JDT: depDate,
            RDT: retDate,
            ACLASS: cabin,
            AD: adult,
            CH: totalChildren,
            INF: infant,
            Umrah: "0",
            TOKEN: token,
            DOBC1: "07-Apr-2017",
            DOBC2: "07-Apr-2017",
            DOBC3: "07-Apr-2017",
            DOBC4: "07-Apr-2017",
        };

        // Delay before search
        await randomDelay(500, 1500);

        console.log(`[AmyBD] Performing API Search...`);
        const raw = await page.evaluate(async (payload, searchUrl, proxyIp) => {
            const resp = await fetch(searchUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-Forwarded-For": proxyIp,
                    "X-Real-IP": proxyIp,
                    "Client-IP": proxyIp
                },
                body: JSON.stringify(payload),
            });
            if (!resp.ok) throw new Error(`HTTP Error: ${resp.status}`);
            return await resp.json();
        }, payload, SEARCH_URL, proxyIp);

        if (!raw || !raw.success) {
            console.error("[AmyBD] Search failed:", raw?.message || "Unknown error");
            return { trips: [], searchId: null, isCompleted: true };
        }

        console.log(`[AmyBD] Found ${(raw.Trips || []).length} flights (SearchID: ${raw.SearchID})`);

        return {
            trips: raw.Trips || [],
            searchId: raw.SearchID || null,
            airlines: raw.Airlines || "",
            isCompleted: true,
            trip,
            adult,
            children: totalChildren,
            infant,
        };

    } catch (err) {
        console.error(`[AmyBD] Scrape Error: ${err.message}`);
        return { trips: [], searchId: null, isCompleted: true };
    } finally {
        await browser.close();
    }
}

module.exports = { scrapeFlights };
