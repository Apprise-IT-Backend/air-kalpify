<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightAlert extends Model
{
    protected $fillable = [
        'from_location',
        'to_location',
        'departure_date',
        'return_date',
        'passengers',
        'email',
        'last_checked_price',
    ];
}
