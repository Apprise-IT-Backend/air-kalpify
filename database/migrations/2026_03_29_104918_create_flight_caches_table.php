<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_caches', function (Blueprint $row) {
            $row->id();
            $row->string('provider', 50);
            $row->char('cache_key', 32)->index(); // md5 of search data
            $row->longText('flights_data');
            $row->string('search_id')->nullable();
            $row->timestamp('expires_at')->index();
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_caches');
    }
};
