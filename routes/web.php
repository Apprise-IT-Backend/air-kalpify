<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AlertController;

Route::get('/', [HomeController::class, 'index']);
Route::post('/search', [FlightController::class, 'search']);
Route::get('/results', [FlightController::class, 'results']);

Route::post('/alerts', [AlertController::class, 'store']);
