const puppeteer = require("puppeteer");

const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";

const BASE_URL = "https://fo-usba.ttinteractive.com/Zenith/FrontOffice";

function buildSearchUrl(sessionPath, { from, to, date, returnDate, adult, child, infant }) {
  const params = new URLSearchParams();
  params.set("OriginAirportCode", from);
  params.set("DestinationAirportCode", to);

  // Format dates as YYYY-MM-DD (the site accepts this format)
  params.set("OutboundDate", date);
  if (returnDate) {
    params.set("InboundDate", returnDate);
  }

  // Traveler types: AD=Adult, CHD=Child, INF=Infant
  params.set("TravelerTypes[0].Key", "AD");
  params.set("TravelerTypes[0].Value", String(adult || 1));
  params.set("TravelerTypes[1].Key", "CHD");
  params.set("TravelerTypes[1].Value", String(child || 0));
  params.set("TravelerTypes[2].Key", "INF");
  params.set("TravelerTypes[2].Value", String(infant || 0));
  params.set("Currency", "BDT");

  return `${BASE_URL}/${sessionPath}/usbangla/en-GB/BookingEngine/SearchResult?${params.toString()}`;
}

async function scrapeFlights(params) {
  const browser = await puppeteer.launch({
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"],
  });

  try {
    const page = await browser.newPage();
    await page.setUserAgent(USER_AGENT);

    // Step 1: Visit home page to get a session ID from the URL
    console.log("[US-Bangla] Getting session...");
    await page.goto(`${BASE_URL}/usbangla/en-GB`, {
      waitUntil: "domcontentloaded",
      timeout: 30000,
    });

    // Extract session path from redirected URL
    // URL pattern: .../FrontOffice/(S(session_id))/usbangla/...
    const currentUrl = page.url();
    const sessionMatch = currentUrl.match(/FrontOffice\/(\(S\([^)]+\)\))/);
    if (!sessionMatch) {
      console.error("[US-Bangla] Could not extract session from URL:", currentUrl);
      return { flights: [], isCompleted: true };
    }
    const sessionPath = sessionMatch[1];
    console.log("[US-Bangla] Session obtained:", sessionPath);

    // Step 2: Navigate to search results
    const searchUrl = buildSearchUrl(sessionPath, params);
    console.log("[US-Bangla] Navigating to search:", searchUrl);

    await page.goto(searchUrl, {
      waitUntil: "networkidle2",
      timeout: 45000,
    });

    // Step 3: Wait for flight results to load
    // The TTInteractive engine renders flight cards in the DOM
    try {
      await page.waitForSelector(
        ".availFlight, .flight-item, .flightResult, .journey-result, [class*='flight'], [class*='avail'], table.flight, .search-result-item, .result-item",
        { timeout: 20000 }
      );
    } catch {
      console.log("[US-Bangla] No flight result selector found, trying to scrape anyway...");
    }

    // Give a moment for any dynamic content to finish rendering
    await new Promise((r) => setTimeout(r, 3000));

    // Step 4: Scrape flight data from the DOM
    const flights = await page.evaluate(() => {
      const results = [];

      // Strategy 1: Look for structured flight data in common TTInteractive patterns
      // Try multiple selector strategies

      // Look for fare/flight containers
      const flightContainers = document.querySelectorAll(
        ".availFlight, .flight-item, .flightResult, .journey-result, " +
        "[class*='flight-result'], [class*='avail-flight'], " +
        ".search-result-item, .result-item, " +
        "tr.flight, tr[class*='avail'], " +
        ".fare-row, .fare-item, " +
        "[data-flight], [data-fare]"
      );

      if (flightContainers.length > 0) {
        flightContainers.forEach((container) => {
          const getText = (selectors) => {
            for (const sel of selectors) {
              const el = container.querySelector(sel);
              if (el) return el.textContent.trim();
            }
            return null;
          };

          const flight = {
            departureTime: getText([
              ".departure-time", ".dept-time", ".dep-time",
              "[class*='depart'] time", "[class*='depart']",
              ".origin-time", ".time-depart"
            ]),
            arrivalTime: getText([
              ".arrival-time", ".arr-time", ".arv-time",
              "[class*='arriv'] time", "[class*='arriv']",
              ".destination-time", ".time-arrive"
            ]),
            duration: getText([
              ".duration", ".flight-duration", ".travel-time",
              "[class*='duration']", "[class*='travel-time']"
            ]),
            price: getText([
              ".price", ".fare", ".amount", ".total-fare",
              "[class*='price']", "[class*='fare']", "[class*='amount']",
              ".cost", ".total"
            ]),
            stops: getText([
              ".stops", ".stop-count", "[class*='stop']",
              ".via", ".connection"
            ]),
            flightNumber: getText([
              ".flight-number", ".flight-no", "[class*='flight-num']",
              ".flt-no"
            ]),
            origin: getText([
              ".origin-code", ".origin .code", "[class*='origin'] .code"
            ]),
            destination: getText([
              ".destination-code", ".dest .code", "[class*='dest'] .code"
            ]),
          };

          if (flight.departureTime || flight.price) {
            results.push(flight);
          }
        });
      }

      // Strategy 2: If no structured containers, try to find data in tables
      if (results.length === 0) {
        const tables = document.querySelectorAll("table");
        tables.forEach((table) => {
          const rows = table.querySelectorAll("tr");
          rows.forEach((row) => {
            const cells = row.querySelectorAll("td");
            if (cells.length >= 3) {
              const text = row.textContent;
              // Look for time patterns (HH:MM) and price patterns
              const timeMatches = text.match(/(\d{1,2}:\d{2})\s*(AM|PM)?/gi);
              const priceMatch = text.match(/[\d,]+(?:\.\d{2})?/);
              if (timeMatches && timeMatches.length >= 2) {
                results.push({
                  departureTime: timeMatches[0],
                  arrivalTime: timeMatches[1],
                  price: priceMatch ? priceMatch[0] : null,
                  duration: null,
                  stops: null,
                  flightNumber: null,
                  origin: null,
                  destination: null,
                });
              }
            }
          });
        });
      }

      // Strategy 3: Look for any JSON data embedded in scripts
      if (results.length === 0) {
        const scripts = document.querySelectorAll("script");
        for (const script of scripts) {
          const content = script.textContent;
          // Look for flight data in JavaScript variables
          const jsonMatch = content.match(/(?:flights|results|journeys|availabilities)\s*[:=]\s*(\[[\s\S]*?\]);/);
          if (jsonMatch) {
            try {
              const data = JSON.parse(jsonMatch[1]);
              if (Array.isArray(data)) {
                data.forEach((item) => {
                  results.push({
                    departureTime: item.departureTime || item.DepartureTime || item.departure || null,
                    arrivalTime: item.arrivalTime || item.ArrivalTime || item.arrival || null,
                    duration: item.duration || item.Duration || item.travelTime || null,
                    price: String(item.price || item.Price || item.fare || item.Fare || item.totalFare || ""),
                    stops: String(item.stops || item.Stops || item.stopCount || 0),
                    flightNumber: item.flightNumber || item.FlightNumber || null,
                    origin: item.origin || item.Origin || null,
                    destination: item.destination || item.Destination || null,
                  });
                });
              }
            } catch {}
          }
        }
      }

      // Also capture the full page HTML for debugging (truncated)
      const bodyText = document.body ? document.body.innerText.substring(0, 5000) : "";

      return { flights: results, bodyPreview: bodyText };
    });

    console.log(`[US-Bangla] Found ${flights.flights.length} flights`);
    if (flights.flights.length === 0) {
      console.log("[US-Bangla] Page preview:", flights.bodyPreview?.substring(0, 500));
    }

    return {
      flights: flights.flights,
      sessionPath,
      searchUrl,
      isCompleted: true,
    };
  } finally {
    await browser.close();
  }
}

module.exports = { scrapeFlights };