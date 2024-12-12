<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/scrape-weather/{city?}', [WeatherController::class, 'postScrape']);
Route::get('/scrape-weather/{city?}', [WeatherController::class, 'scrape']);