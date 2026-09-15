<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FlightAlert;
use Illuminate\Support\Facades\Log;

class CheckFlightPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:flight-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loop through flight alerts and check if the price has dropped.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alerts = FlightAlert::all();

        if ($alerts->isEmpty()) {
            $this->info('No flight alerts found.');
            return;
        }

        $this->info('Checking prices for ' . $alerts->count() . ' alerts...');

        foreach ($alerts as $alert) {
            // Mock price check logic
            // Simulate a new price between 100 and 500
            $newPrice = rand(100, 500);

            // If last_checked_price is null, set it to a high number to simulate a drop
            $currentSavedPrice = $alert->last_checked_price ?? 9999.99;

            if ($newPrice < $currentSavedPrice) {
                // Simulate sending an email or logging the drop
                Log::info("Price dropped for your route {$alert->from_location} -> {$alert->to_location}! New price: \${$newPrice}");
                $this->info("Price drop logged for alert ID {$alert->id} ({$alert->email})");

                // Update the database with the new lowest price
                $alert->update(['last_checked_price' => $newPrice]);
            } else {
                $this->line("No price drop for alert ID {$alert->id} ({$alert->email}).");
            }
        }

        $this->info('Price check completed.');
    }
}
