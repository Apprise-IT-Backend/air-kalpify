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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            
            // Search Parameters
            $table->string('from_location', 10);
            $table->string('to_location', 10);
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            $table->string('cabin_class')->default('Economy');
            $table->string('trip_type')->default('one-way');
            $table->string('provider');
            
            // Result data
            $table->longText('results'); // Result blob (JSON)
            $table->timestamp('search_at'); // When results were scraped 
            
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['from_location', 'to_location', 'departure_date', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
