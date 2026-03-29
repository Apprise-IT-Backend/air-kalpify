<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightCache extends Model
{
    protected $fillable = ['provider', 'cache_key', 'flights_data', 'search_id', 'expires_at'];

    protected $casts = [
        'flights_data' => 'array',
        'expires_at'   => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
