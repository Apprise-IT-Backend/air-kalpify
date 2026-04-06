<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AlertController;

Route::get('/', [HomeController::class, 'index']);
Route::post('/search', [FlightController::class, 'search']);
Route::get('/results', [FlightController::class, 'results']);
Route::get('/api/search-flights/{provider}', [FlightController::class, 'fetchProvider']);
Route::get('/api/db-flights/{provider}', [FlightController::class, 'fetchProviderFromDb']);

Route::post('/alerts', [AlertController::class, 'store']);
