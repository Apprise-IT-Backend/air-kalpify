const fs = require('fs');
const readline = require('readline');

async function processAirports() {
    const fileStream = fs.createReadStream('public/airport-codes.csv');
    const rl = readline.createInterface({
        input: fileStream,
        crlfDelay: Infinity
    });

    let headers = [];
    let isFirstLine = true;
    let validAirports = [];

    for await (const line of rl) {
        if (isFirstLine) {
            headers = line.split(',');
            isFirstLine = false;
            continue;
        }

        // Use a simple regex to split by comma but ignore commas inside quotes
        const values = line.split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/);
        
        const type = values[1];
        const name = values[2] ? values[2].replace(/"/g, '') : '';
        const iso_country = values[5];
        const municipality = values[7] ? values[7].replace(/"/g, '') : '';
        const iata_code = values[9] ? values[9].replace(/"/g, '') : '';

        // Only include those with an IATA code and that are large or medium airports
        if (iata_code && (type === 'large_airport' || type === 'medium_airport')) {
            validAirports.push({
                code: iata_code,
                name: name,
                city: municipality,
                country: iso_country
            });
        }
    }

    fs.writeFileSync('public/airports_search.json', JSON.stringify(validAirports));
    console.log(`Processed ${validAirports.length} valid airports.`);
}

processAirports();
