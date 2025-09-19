<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class WeatherController extends Controller
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

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

        $latitude = (float) $request->input('lat');
        $longitude = (float) $request->input('lon');

        try {
            // Fetch weather data using the service (with caching)
            $weatherData = $this->weatherService->fetchWeatherData($latitude, $longitude);

            return response()->json($weatherData);

        } catch (Exception $e) {
            Log::error('Weather API error in controller', [
                'lat' => $latitude,
                'lon' => $longitude,
                'error' => $e->getMessage()
            ]);

            // Return appropriate error response based on exception message
            if (str_contains($e->getMessage(), 'configuration error')) {
                return response()->json([
                    'error' => 'Weather service is not properly configured'
                ], 503);
            }

            if (str_contains($e->getMessage(), 'Location not found')) {
                return response()->json([
                    'error' => 'Location not found'
                ], 404);
            }

            if (str_contains($e->getMessage(), 'rate limiting')) {
                return response()->json([
                    'error' => 'Weather service temporarily unavailable due to high demand'
                ], 429);
            }

            if (str_contains($e->getMessage(), 'Invalid latitude') || str_contains($e->getMessage(), 'Invalid longitude')) {
                return response()->json([
                    'error' => 'Invalid coordinates provided'
                ], 400);
            }

            // Generic error for other cases
            return response()->json([
                'error' => 'Weather data is currently unavailable. Please try again later.'
            ], 503);
        }
    }
}
