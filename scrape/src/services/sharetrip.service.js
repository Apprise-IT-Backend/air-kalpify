const USER_AGENT =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";

const HEADERS = {
  "User-Agent": USER_AGENT,
  "Origin": "https://sharetrip.net",
  "Referer": "https://sharetrip.net/",
  "Accept": "application/json, text/plain, */*",
  "Accept-Language": "en-US,en;q=0.9",
  "sec-ch-ua": '"Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
  "sec-ch-ua-mobile": "?0",
  "sec-ch-ua-platform": '"Windows"',
  "sec-fetch-dest": "empty",
  "sec-fetch-mode": "cors",
  "sec-fetch-site": "same-site",
};

function buildInitApiUrl(params) {
  const { from, to, date, returnDate, adult, child, kids, infant, cabin_class } = params;
  const tripType = returnDate ? "RETURN" : "ONEWAY";
  const cabin = cabin_class.toUpperCase();
  
  const searchParams = new URLSearchParams();
  searchParams.append("cabinClass", cabin);
  searchParams.append("currency", "BDT");
  searchParams.append("departureDates[]", date);
  if (returnDate) searchParams.append("departureDates[]", returnDate);
  searchParams.append("destinations[]", to);
  if (returnDate) searchParams.append("destinations[]", from);
  searchParams.append("numOfAdult", adult);
  searchParams.append("numOfChild", child || 0);
  searchParams.append("numOfInfant", infant || 0);
  searchParams.append("numOfKid", kids || 0);
  searchParams.append("occupation", "NOT_SELECTED");
  searchParams.append("origins[]", from);
  if (returnDate) searchParams.append("origins[]", to);
  searchParams.append("tripType", tripType);
  
  return `https://api.sharetrip.net/api/v2/flight/search/initialize?${searchParams.toString()}`;
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
        headers: { "Content-Type": "application/json", ...HEADERS },
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
                headers: { "Content-Type": "application/json", ...HEADERS },
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

  // PHASE 1: Initialization using direct API fetch
  const apiUrl = buildInitApiUrl(params);
  
  try {
    const initRes = await fetch(apiUrl, {
      headers: HEADERS
    });
    
    if (!initRes.ok) {
      throw new Error(`ShareTrip API returned ${initRes.status} ${initRes.statusText}`);
    }
    
    const initJson = await initRes.json();
    const capturedSearchId = initJson?.response?.searchId || null;
    
    return { response: { searchId: capturedSearchId, matchedFlights: [], isCompleted: false } };
  } catch (err) {
    console.error(`[ShareTrip] Fatal error in Phase 1 setup: ${err.message}`);
    return { response: { searchId: null, matchedFlights: [], isCompleted: false } };
  }
}

module.exports = { scrapeFlights };
