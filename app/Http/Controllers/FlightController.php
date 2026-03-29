<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;


use Illuminate\Support\Facades\Http;

class FlightController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'from_location'  => 'required|string|max:10',
            'to_location'    => 'required|string|max:10',
            'departure_date' => 'required|date',
            'return_date'    => 'nullable|date|after_or_equal:departure_date',
            'adults'         => 'required|integer|min:1|max:9',
            'children'       => 'nullable|integer|min:0|max:9',
            'infants'        => 'nullable|integer|min:0|max:9',
            'cabin_class'    => 'nullable|string|in:Economy,Business,First',
            'trip_type'      => 'nullable|string|in:one-way,round-way,multi-city',
        ]);

        // Normalise optional fields
        $validated['children']    = $validated['children']    ?? 0;
        $validated['infants']     = $validated['infants']     ?? 0;
        $validated['cabin_class'] = $validated['cabin_class'] ?? 'Economy';
        $validated['trip_type']   = $validated['trip_type']   ?? 'one-way';
        // Keep a combined passenger count for backward-compat
        $validated['passengers']  = $validated['adults'] + $validated['children'] + $validated['infants'];

        $request->session()->put('flight_search', $validated);

        return redirect('/results');
    }

    public function results(Request $request)
    {
        $searchData = $request->session()->get('flight_search');

        if (!$searchData) {
            return redirect('/')->withErrors(['error' => 'Please perform a search first.']);
        }

        $providers = ['gozayaan', 'sharetrip'];

        // Return the view immediately without fetching
        return view('results', compact('searchData', 'providers'));
    }

    public function fetchProvider(Request $request, $provider)
    {
        $searchData = $request->session()->get('flight_search');

        if (!$searchData) {
            return response()->json(['success' => false, 'error' => 'No search session found.'], 400);
        }

        try {
            $response = Http::timeout(60)->get('http://localhost:3000/api/flights', [
                'from'       => $searchData['from_location'],
                'to'         => $searchData['to_location'],
                'date'       => $searchData['departure_date'],
                'adult'      => $searchData['adults']   ?? $searchData['passengers'],
                'child'      => $searchData['children'] ?? 0,
                'infant'     => $searchData['infants']  ?? 0,
                'cabin'      => $searchData['cabin_class'] ?? 'Economy',
                'tripType'   => $searchData['trip_type']   ?? 'one-way',
                'provider'   => $provider,
                'returnDate' => $searchData['return_date'] ?? null,
                'search_id'  => $request->query('search_id'),
            ]);

            if ($response->failed()) {
                return response()->json(['success' => false, 'error' => 'Failed to fetch from ' . $provider], 500);
            }

            $apiData = $response->json();
            $flights = $this->transformApiResponse($apiData, $provider);

            return response()->json([
                'success'     => true,
                'provider'    => $provider,
                'flights'     => $flights,
                'search_id'   => $apiData['search_id'] ?? null,
                'isCompleted' => $apiData['isCompleted'] ?? false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    private function transformApiResponse($apiData, $provider)
    {
        $flights = $apiData['flights'] ?? [];
        $flatFlights = [];

        $providerKey = strtolower($provider);
        $otaName = $this->getProviderName($providerKey);
        $otaColor = $this->getProviderColor($providerKey);

        foreach ($flights as $flight) {
            
            // --- DEPARTURE ---
            $depTime = $flight['departure']['departure'] ?? '';
            $arrTime = $flight['departure']['arrival'] ?? '';
            try {
                $depTimeParsed = Carbon::parse($depTime)->format('h:i A');
                $arrTimeParsed = Carbon::parse($arrTime)->format('h:i A');
            } catch (\Exception $e) {
                $depTimeParsed = $depTime; $arrTimeParsed = $arrTime;
            }

            $durationStr = $flight['departure']['duration'] ?? '0h 0m';
            $durationMinutes = 0;
            if (preg_match('/(\d+)h/', $durationStr, $m)) $durationMinutes += $m[1] * 60;
            if (preg_match('/(\d+)m/', $durationStr, $m)) $durationMinutes += $m[1];

            $stopsStr = $flight['departure']['stops'] ?? 'Non Stop';
            $stopsCount = ($stopsStr === 'Non Stop' || $stopsStr === 'Direct') ? 0 : (int)$stopsStr;

            // --- RETURN (Optional) ---
            $returnLeg = null;
            if (isset($flight['return']['departure']) && !empty($flight['return']['departure'])) {
                $retDepTime = $flight['return']['departure'] ?? '';
                $retArrTime = $flight['return']['arrival'] ?? '';
                try {
                    $retDepTimeParsed = Carbon::parse($retDepTime)->format('h:i A');
                    $retArrTimeParsed = Carbon::parse($retArrTime)->format('h:i A');
                } catch (\Exception $e) {
                    $retDepTimeParsed = $retDepTime; $retArrTimeParsed = $retArrTime;
                }

                $retDurationStr = $flight['return']['duration'] ?? '0h 0m';
                $retDurationMinutes = 0;
                if (preg_match('/(\d+)h/', $retDurationStr, $m)) $retDurationMinutes += $m[1] * 60;
                if (preg_match('/(\d+)m/', $retDurationStr, $m)) $retDurationMinutes += $m[1];

                $returnLeg = [
                    'airline'        => $flight['return']['airline'] ?? ($flight['departure']['airline'] ?? 'Unknown'),
                    'airline_logo'   => $flight['return']['logo'] ?? ($flight['departure']['logo'] ?? null),
                    'departure_time' => $retDepTimeParsed,
                    'arrival_time'   => $retArrTimeParsed,
                    'duration'       => $retDurationStr,
                    'stops'          => $flight['return']['stops'] ?? 'Non Stop',
                    'origin'         => $flight['return']['origin'] ?? null,
                    'destination'    => $flight['return']['destination'] ?? null,
                ];
                
                // Add return duration to total for sorting/best score
                $durationMinutes += $retDurationMinutes;
            }

            $price = $flight['discountedPrice'] ?? $flight['totalPrice'] ?? 0;

            $flatFlights[] = [
                'airline'          => $flight['departure']['airline'] ?? 'Unknown Airline',
                'airline_logo'     => $flight['departure']['logo'] ?? null,
                'departure_time'   => $depTimeParsed,
                'arrival_time'     => $arrTimeParsed,
                'duration'         => $durationStr,
                'duration_minutes' => $durationMinutes,
                'price'            => $price,
                'currency'         => $flight['currency'] ?? 'BDT',
                'stops'            => $stopsStr,
                'stops_count'      => $stopsCount,
                'ota_name'         => $otaName,
                'ota_color'        => $otaColor,
                'is_round_trip'    => !is_null($returnLeg),
                'return_leg'       => $returnLeg,
                'search_id'        => $apiData['search_id'] ?? null,
                'fare_id'          => $flight['fare_id'] ?? null,
                'sequence_code'    => $flight['sequenceCode'] ?? null,
                'provider'         => $providerKey,
            ];
        }

        // Sort by cheapest first
        usort($flatFlights, function ($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        
        return $flatFlights;
    }

    private function getProviderName($key)
    {
        $names = [
            'gozayaan'     => 'GoZayaan',
            'sharetrip'    => 'ShareTrip',
            'flightexpert' => 'FlightExpert',
            'airtickets'   => 'AirTickets',
        ];
        return $names[$key] ?? ucfirst($key);
    }

    private function getProviderColor($provider)
    {
        $colors = [
            'gozayaan'     => '#00b4d8',
            'sharetrip'    => '#f77f00',
            'flightexpert' => '#06d6a0',
            'airtickets'   => '#7209b7',
        ];
        
        return $colors[strtolower($provider)] ?? '#6c757d';
    }

    private function generateDynamicFlights($searchData)
    {
        $originCode = strtoupper(substr($searchData['from_location'], 0, 3));
        $destCode   = strtoupper(substr($searchData['to_location'], 0, 3));

        $seed = crc32($originCode . $destCode . $searchData['departure_date']);
        srand($seed);

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

        $allFlights = [];

        foreach ($otas as $ota) {
            $numFlights = rand(1, 3);

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

                $allFlights[] = [
                    'airline'        => $airline,
                    'departure_time' => $departureTime->format('h:i A'),
                    'arrival_time'   => $arrivalTime->format('h:i A'),
                    'duration'       => $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : ''),
                    'price'          => round($finalPrice, 2),
                    'stops'          => rand(0, 10) > 8 ? '1 Stop' : 'Direct',
                    'ota_name'       => $ota['name'],
                    'ota_color'      => $ota['color'],
                    'currency'       => 'USD', // Dynamic generator used USD before potentially
                ];
            }
        }

        // Sort by cheapest first
        usort($allFlights, fn($a, $b) => $a['price'] <=> $b['price']);

        srand();
        return $allFlights;
    }
}
