<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class FlightController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'from_location' => 'required|string|max:100',
            'to_location' => 'required|string|max:100',
            'departure_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:departure_date',
            'passengers' => 'required|integer|min:1|max:10',
        ]);

        // Store the search data in the session
        $request->session()->put('flight_search', $validated);

        return redirect('/results');
    }

    public function results(Request $request)
    {
        $searchData = $request->session()->get('flight_search');

        if (!$searchData) {
            return redirect('/')->withErrors(['error' => 'Please perform a search first.']);
        }

        $otaFlights = $this->generateDynamicFlights($searchData);

        return view('results', compact('searchData', 'otaFlights'));
    }

    private function generateDynamicFlights($searchData)
    {
        $originCode = strtoupper(substr($searchData['from_location'], 0, 3));
        $destCode   = strtoupper(substr($searchData['to_location'], 0, 3));

        $seed = crc32($originCode . $destCode . $searchData['departure_date']);
        srand($seed);

        // OTA list with brand colours
        $otas = [
            ['name' => 'GoZayaan',    'color' => '#00b4d8'],
            ['name' => 'ShareTrip',   'color' => '#f77f00'],
            ['name' => 'Flightexpert','color' => '#06d6a0'],
            ['name' => 'Airtickets',  'color' => '#7209b7'],
        ];

        $baseAirlines = [
            'Biman Bangladesh', 'US-Bangla Airlines', 'Novoair',
            'Air Arabia', 'Emirates', 'IndiGo', 'FlyDubai',
        ];

        $routeFactor        = (abs(ord($originCode[0]) - ord($destCode[0])) + 1) * 1.5;
        $basePrice          = 100 + ($routeFactor * 10);
        $baseDurationMinutes = 60 + ($routeFactor * 40);

        $otaFlights = [];

        foreach ($otas as $ota) {
            $flights = [];
            $numFlights = rand(1, 3); // max 3 per OTA

            for ($i = 0; $i < $numFlights; $i++) {
                $airline = $baseAirlines[array_rand($baseAirlines)];

                $departHour        = rand(5, 22);
                $departMinuteValue = [0, 15, 30, 45][array_rand([0, 15, 30, 45])];
                $departureTime     = Carbon::createFromTime($departHour, $departMinuteValue);

                $durationMinutes = $baseDurationMinutes + rand(-30, 30);
                $arrivalTime     = $departureTime->copy()->addMinutes($durationMinutes);

                $priceMultiplier = 1.0;
                if ($airline === 'IndiGo')  $priceMultiplier = 0.8;
                if ($airline === 'Emirates') $priceMultiplier = 1.5;
                if ($departHour < 7 || $departHour > 20) $priceMultiplier -= 0.1;

                $finalPrice = ($basePrice * $priceMultiplier) + rand(-20, 50);

                $hours   = floor($durationMinutes / 60);
                $minutes = $durationMinutes % 60;

                $flights[] = [
                    'airline'        => $airline,
                    'departure_time' => $departureTime->format('h:i A'),
                    'arrival_time'   => $arrivalTime->format('h:i A'),
                    'duration'       => $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : ''),
                    'price'          => round($finalPrice, 2),
                    'stops'          => rand(0, 10) > 8 ? '1 Stop' : 'Direct',
                ];
            }

            // Sort each OTA's flights cheapest first
            usort($flights, fn($a, $b) => $a['price'] <=> $b['price']);

            $otaFlights[] = [
                'ota'     => $ota['name'],
                'color'   => $ota['color'],
                'flights' => $flights,
            ];
        }

        srand();
        return $otaFlights;
    }
}
