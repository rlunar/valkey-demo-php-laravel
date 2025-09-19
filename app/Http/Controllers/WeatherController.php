<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WeatherController extends Controller
{
    /**
     * Get current weather data for given coordinates
     */
    public function getCurrentWeather(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ], [
            'lat.required' => 'Latitude is required',
            'lat.numeric' => 'Latitude must be a valid number',
            'lat.between' => 'Latitude must be between -90 and 90 degrees',
            'lon.required' => 'Longitude is required',
            'lon.numeric' => 'Longitude must be a valid number',
            'lon.between' => 'Longitude must be between -180 and 180 degrees',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid coordinates provided',
                'details' => $validator->errors()
            ], 400);
        }

        $latitude = $request->input('lat');
        $longitude = $request->input('lon');

        // Validate API key is configured
        if (empty(config('weather.api_key'))) {
            return response()->json([
                'error' => 'Weather service is not properly configured'
            ], 503);
        }

        // TODO: Implement weather service integration in next task
        // For now, return a placeholder response with the provided coordinates
        return response()->json([
            'location' => 'Sample Location',
            'temperature' => 22,
            'condition' => 'Clear',
            'description' => 'clear sky',
            'icon' => '01d',
            'humidity' => 65,
            'windSpeed' => 3.5,
            'coordinates' => [
                'lat' => $latitude,
                'lon' => $longitude,
            ],
            'lastUpdated' => now()->toISOString(),
        ]);
    }
}
