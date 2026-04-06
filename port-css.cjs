const fs = require('fs');

const home = fs.readFileSync('resources/views/home.blade.php', 'utf-8');
let results = fs.readFileSync('resources/views/results.blade.php', 'utf-8');

// Extract CSS
const cssStart = home.indexOf('/* ===== SEARCH PANEL ===== */');
const cssEnd = home.indexOf('/* Popular destinations section */');
const searchCss = home.substring(cssStart, cssEnd);

// Extract HTML
const htmlStart = home.indexOf('<!-- Search Panel -->');
const htmlEnd = home.indexOf('<!-- ============================', htmlStart);
let searchHtml = home.substring(htmlStart, htmlEnd);

// Adapt HTML for results
searchHtml = searchHtml.replace('id="flightSearchForm"', 'id="modifySearchForm"');
searchHtml = searchHtml.replace('class="search-panel-wrapper"', 'class="search-panel-wrapper w-100 mx-auto" style="margin-top: 0; position: relative; z-index: 5;"');
searchHtml = searchHtml.replace('class="search-panel-inner"', 'class="search-panel-inner" style="box-shadow: none; border: none; padding: 0;"');
searchHtml = searchHtml.replace('margin-top: 80px;', '');

// Extract JS
const jsStart = home.indexOf('// ===== TRAVELER COUNTS =====');
const jsEnd = home.indexOf('// --- Trip type tabs ---');
const jsExtraStart = home.indexOf('// --- Trip type tabs ---', home);
const jsExtraEnd = home.indexOf('// --- Scroll reveal ---');
let searchJs = home.substring(jsStart, jsExtraEnd);

// Remove 'kids' entirely from JS
searchJs = searchJs.replace(/,\s*kids:\s*0/g, '');
searchJs = searchJs.replace(/\+\s*counts\.kids\s*/g, '');
searchJs = searchJs.replace(/if \(type === 'adults' && counts\.adults \+ delta < 1\) return;\n\s*if \(counts\[type\] \+ delta < 0\) return;\n/g, 
  `if (type === 'adults' && counts.adults + delta < 1) return;
                if (counts[type] + delta < 0) return;
`);

// Inject CSS into results
results = results.replace('</style>', `
${searchCss}
</style>`);

// Inject HTML into results
const replaceStart = results.indexOf('{{-- Modify Search Inline Panel --}}');
const replaceEnd = results.indexOf('</div>', results.indexOf('</form>')) + 6 + 7; // up to closing of modifyPanel
results = results.substring(0, replaceStart) + 
`{{-- Modify Search Inline Panel --}}
    <div class="modify-search-panel" id="modifyPanel" style="padding: 15px 24px;">
        ${searchHtml}
    </div>
` + results.substring(replaceEnd);

// Inject JS into results
results = results.replace('// ── Listeners ──', `
    ${searchJs}
    // ── Listeners ──`);

// Remove "Kids" from results top bar string
results = results.replace(/@if\(\(\$searchData\['kids'\] \?\? 0\) > 0\), \{\{ \$searchData\['kids'\] \}\}K @endif\s*/g, '');

fs.writeFileSync('resources/views/results.blade.php', results);
console.log('results.blade.php updated successfully.');
