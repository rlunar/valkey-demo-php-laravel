<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Weather API routes
Route::prefix('weather')->group(function () {
    Route::get('/random', [WeatherController::class, 'getMultipleRandomWeather']);
    Route::get('/single', [WeatherController::class, 'getRandomWeather']);
});
