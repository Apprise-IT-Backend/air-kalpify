<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $fillable = [
        'from_location',
        'to_location',
        'departure_date',
        'return_date',
        'adults',
        'children',
        'infants',
        'cabin_class',
        'trip_type',
        'provider',
        'results',
        'search_at',
    ];

    protected $casts = [
        'results'   => 'array',
        'search_at' => 'datetime',
    ];
}
