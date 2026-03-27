<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flight_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('from_location');
            $table->string('to_location');
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->integer('passengers');
            $table->string('email');
            $table->decimal('last_checked_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_alerts');
    }
};
