<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FlightAlert;

class AlertController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'from_location' => 'required|string|max:100',
            'to_location' => 'required|string|max:100',
            'departure_date' => 'required|date',
            'return_date' => 'nullable|date',
            'passengers' => 'required|integer',
        ]);

        // Prevent duplicate alerts
        $exists = FlightAlert::where('email', $validated['email'])
            ->where('from_location', $validated['from_location'])
            ->where('to_location', $validated['to_location'])
            ->where('departure_date', $validated['departure_date'])
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'You already have a price alert for this route and date.');
        }

        FlightAlert::create($validated);

        return redirect()->back()->with('success', 'Price alert set successfully!');
    }
}
