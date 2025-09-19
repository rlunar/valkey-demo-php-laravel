<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use App\Services\WeatherConfigService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;
use InvalidArgumentException;

class WeatherController extends Controller
{
    // Don't inject WeatherService in constructor to avoid config validation issues
    // Instead, resolve it when needed in methods

    /**
     * Get weather widget configuration
     */
    public function getConfig(): JsonResponse
    {
        try {
            // Try to get validated config first - this will throw if there are critical issues
            $config = WeatherConfigService::getValidatedConfig();

            if (!$config['widget']['enabled']) {
                return response()->json([
                    'error' => 'Weather widget is disabled'
                ], 503);
            }

            return response()->json([
                'default_location' => $config['default_location'],
                'widget' => [
                    'auto_refresh_interval' => $config['widget']['auto_refresh_interval'],
                    'show_detailed_info' => $config['widget']['show_detailed_info'],
                    'temperature_unit' => $config['widget']['temperature_unit'],
                ],
            ]);

        } catch (InvalidArgumentException $e) {
            Log::error('Weather config validation error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Weather configuration is not available'
            ], 503);
        } catch (Exception $e) {
            Log::error('Weather config endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Weather configuration is not available'
            ], 503);
        }
    }

    /**
     * Get current weather data for given coordinates
     */
    public function getCurrentWeather(Request $request): JsonResponse
    {
        $requestId = uniqid('weather_', true);
        $startTime = microtime(true);

        Log::info('Weather API request received', [
            'request_id' => $requestId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'params' => $request->all(),
        ]);

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
            $duration = (microtime(true) - $startTime) * 1000;

            Log::warning('Weather API validation failed', [
                'request_id' => $requestId,
                'errors' => $validator->errors()->toArray(),
                'duration_ms' => round($duration, 2),
            ]);

            return response()->json([
                'error' => 'Invalid coordinates provided',
                'details' => $validator->errors()
            ], 400);
        }

        $latitude = (float) $request->input('lat');
        $longitude = (float) $request->input('lon');

        try {
            Log::debug('Fetching weather data', [
                'request_id' => $requestId,
                'lat' => $latitude,
                'lon' => $longitude,
            ]);

            // Resolve WeatherService when needed to avoid constructor issues
            $weatherService = app(WeatherService::class);

            // Fetch weather data using the service (with caching)
            $weatherData = $weatherService->fetchWeatherData($latitude, $longitude);

            $duration = (microtime(true) - $startTime) * 1000;

            Log::info('Weather API request successful', [
                'request_id' => $requestId,
                'lat' => $latitude,
                'lon' => $longitude,
                'location' => $weatherData['location'] ?? 'Unknown',
                'temperature' => $weatherData['temperature'] ?? null,
                'duration_ms' => round($duration, 2),
            ]);

            return response()->json($weatherData);

        } catch (Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            Log::error('Weather API error in controller', [
                'request_id' => $requestId,
                'lat' => $latitude,
                'lon' => $longitude,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'duration_ms' => round($duration, 2),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return appropriate error response based on exception message
            if (str_contains($e->getMessage(), 'configuration error')) {
                Log::critical('Weather service configuration error detected', [
                    'request_id' => $requestId,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'error' => 'Weather service is not properly configured'
                ], 503);
            }

            if (str_contains($e->getMessage(), 'Location not found')) {
                Log::info('Location not found for coordinates', [
                    'request_id' => $requestId,
                    'lat' => $latitude,
                    'lon' => $longitude,
                ]);

                return response()->json([
                    'error' => 'Location not found'
                ], 404);
            }

            if (str_contains($e->getMessage(), 'rate limiting')) {
                Log::warning('Weather API rate limit exceeded', [
                    'request_id' => $requestId,
                    'lat' => $latitude,
                    'lon' => $longitude,
                ]);

                return response()->json([
                    'error' => 'Weather service temporarily unavailable due to high demand'
                ], 429);
            }

            if (str_contains($e->getMessage(), 'Invalid latitude') || str_contains($e->getMessage(), 'Invalid longitude')) {
                Log::warning('Invalid coordinates provided to weather service', [
                    'request_id' => $requestId,
                    'lat' => $latitude,
                    'lon' => $longitude,
                ]);

                return response()->json([
                    'error' => 'Invalid coordinates provided'
                ], 400);
            }

            // Generic error for other cases
            Log::error('Unhandled weather service error', [
                'request_id' => $requestId,
                'lat' => $latitude,
                'lon' => $longitude,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Weather data is currently unavailable. Please try again later.'
            ], 503);
        }
    }
}
