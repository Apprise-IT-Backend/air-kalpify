/**
 * One-shot: Open amybd.com/flights, intercept the first POST to atapi.aspx,
 * and print the full request body so we can see the real TOKEN value.
 */
const puppeteer = require("puppeteer");

(async () => {
  const browser = await puppeteer.launch({
    headless: true,
    args: ["--no-sandbox", "--disable-setuid-sandbox"],
  });

  const page = await browser.newPage();

  await page.setUserAgent(
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36"
  );

  // Intercept requests to sniff the TOKEN
  let captured = null;

  page.on("request", (req) => {
    if (req.url().includes("atapi.aspx")) {
      const body = req.postData();
      console.log("[REQUEST] atapi.aspx body:", body);
      captured = body;
    }
  });

  page.on("response", async (res) => {
    if (res.url().includes("atapi.aspx")) {
      try {
        const json = await res.json();
        console.log("[RESPONSE] atapi.aspx:", JSON.stringify(json).substring(0, 300));
      } catch {}
    }
  });

  console.log("Opening home page…");
  await page.goto("https://www.amybd.com/flights", {
    waitUntil: "networkidle2",
    timeout: 30000,
  });

  // Dump all JS variables that include "TOKEN" or "amyTK"
  const vars = await page.evaluate(() => {
    const result = {};
    try { result.amyTK = window.amyTK; } catch {}
    try { result.TOKEN = window.TOKEN; } catch {}
    try { result.gTOKEN = window.gTOKEN; } catch {}
    // Search for anything that looks like a token in window scope
    for (const key of Object.keys(window)) {
      const val = window[key];
      if (typeof val === "string" && val.length >= 16 && /^[A-Za-z0-9+/=]+$/.test(val)) {
        result[key] = val;
      }
    }
    return result;
  });
  console.log("\n[WINDOW VARS]", JSON.stringify(vars, null, 2));

  // Now trigger a flight search programmatically via page.evaluate
  const apiResult = await page.evaluate(async () => {
    const payload = {
      is_combo: 0,
      CMND: "_FLIGHTSEARCHOPEN_",
      TRIP: "OW",
      FROM: "Dhaka - DAC - BANGLADESH",
      DEST: "Coxs Bazar - CXB - BANGLADESH",
      JDT: "15-Apr-2026",
      RDT: "15-Apr-2026",
      ACLASS: "Y",
      AD: 1, CH: 0, INF: 0,
      Umrah: "0",
      TOKEN: window.amyTK || window.TOKEN || window.gTOKEN || "",
      DOBC1: "07-Apr-2017", DOBC2: "07-Apr-2017",
      DOBC3: "07-Apr-2017", DOBC4: "07-Apr-2017",
    };
    console.log("Sending payload TOKEN:", payload.TOKEN);
    const resp = await fetch("https://www.amybd.com/atapi.aspx", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(payload),
    });
    return { status: resp.status, body: await resp.text() };
  });

  console.log("\n[API RESULT status]", apiResult.status);
  console.log("[API RESULT body]", apiResult.body.substring(0, 500));

  await browser.close();
})();
