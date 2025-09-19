<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class WeatherService
{
    private string $apiKey;
    private string $apiUrl;
    private int $cacheTtl;
    private int $retryAttempts;

    public function __construct()
    {
        // Use WeatherConfigService for validated configuration
        $apiConfig = WeatherConfigService::getApiConfig();
        $cacheConfig = WeatherConfigService::getCacheConfig();

        $this->apiKey = $apiConfig['api_key'];
        $this->apiUrl = $apiConfig['api_url'];
        $this->cacheTtl = $cacheConfig['cache_ttl'];
        $this->retryAttempts = $cacheConfig['retry_attempts'];

        if (empty($this->apiKey)) {
            throw new Exception('OpenWeather API key is not configured');
        }
    }

    /**
     * Fetch weather data for given coordinates
     *
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array Formatted weather data
     * @throws Exception
     */
    public function fetchWeatherData(float $lat, float $lon): array
    {
        // Validate coordinates
        $this->validateCoordinates($lat, $lon);

        // Generate cache key
        $cacheKey = $this->generateCacheKey($lat, $lon);

        // Try to get cached data first
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            Log::info('Weather data retrieved from cache', ['lat' => $lat, 'lon' => $lon]);
            return $cachedData;
        }

        // Fetch fresh data from API
        $weatherData = $this->fetchFromApi($lat, $lon);

        // Cache the data
        Cache::put($cacheKey, $weatherData, $this->cacheTtl);

        Log::info('Weather data fetched and cached', ['lat' => $lat, 'lon' => $lon]);

        return $weatherData;
    }

    /**
     * Fetch weather data from OpenWeather API with retry logic
     *
     * @param float $lat
     * @param float $lon
     * @return array
     * @throws Exception
     */
    private function fetchFromApi(float $lat, float $lon): array
    {
        $attempt = 0;
        $lastException = null;
        $startTime = microtime(true);

        Log::info('Starting weather API request', [
            'lat' => $lat,
            'lon' => $lon,
            'max_attempts' => $this->retryAttempts,
            'api_url' => $this->apiUrl,
        ]);

        while ($attempt < $this->retryAttempts) {
            $attemptStartTime = microtime(true);
            $attempt++;

            try {
                Log::debug("Weather API attempt {$attempt}/{$this->retryAttempts}", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'attempt' => $attempt,
                ]);

                $response = Http::timeout(10)
                    ->get("{$this->apiUrl}/weather", [
                        'lat' => $lat,
                        'lon' => $lon,
                        'appid' => $this->apiKey,
                        'units' => 'metric', // Use Celsius
                    ]);

                $attemptDuration = (microtime(true) - $attemptStartTime) * 1000; // Convert to milliseconds

                if ($response->successful()) {
                    $data = $response->json();

                    Log::info('Weather API request successful', [
                        'lat' => $lat,
                        'lon' => $lon,
                        'attempt' => $attempt,
                        'duration_ms' => round($attemptDuration, 2),
                        'total_duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                        'response_size' => strlen($response->body()),
                    ]);

                    if (!is_array($data)) {
                        throw new Exception('Invalid response format from weather API');
                    }
                    return $this->formatWeatherData($data);
                }

                Log::warning('Weather API returned error status', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'duration_ms' => round($attemptDuration, 2),
                    'response_body' => $response->body(),
                ]);

                // Handle specific HTTP error codes - don't retry for certain errors
                if (in_array($response->status(), [401, 404, 429])) {
                    $this->handleApiError($response->status(), $response->body());
                }

                // For other errors, treat as retryable
                throw new Exception("HTTP {$response->status()}: {$response->body()}");

            } catch (Exception $e) {
                $attemptDuration = (microtime(true) - $attemptStartTime) * 1000;

                // If it's a specific API error (401, 404, 429), re-throw immediately
                if (str_contains($e->getMessage(), 'Weather service configuration error') ||
                    str_contains($e->getMessage(), 'Location not found') ||
                    str_contains($e->getMessage(), 'rate limiting')) {

                    Log::error('Weather API non-retryable error', [
                        'lat' => $lat,
                        'lon' => $lon,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                        'duration_ms' => round($attemptDuration, 2),
                    ]);

                    throw $e;
                }

                $lastException = $e;

                Log::warning('Weather API request failed', [
                    'attempt' => $attempt,
                    'max_attempts' => $this->retryAttempts,
                    'lat' => $lat,
                    'lon' => $lon,
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                    'duration_ms' => round($attemptDuration, 2),
                ]);

                if ($attempt < $this->retryAttempts) {
                    // Exponential backoff with jitter
                    $baseDelay = pow(2, $attempt - 1); // 1, 2, 4 seconds
                    $jitter = rand(0, 1000) / 1000; // 0-1 second jitter
                    $delay = $baseDelay + $jitter;

                    Log::info("Retrying weather API request in {$delay} seconds", [
                        'lat' => $lat,
                        'lon' => $lon,
                        'next_attempt' => $attempt + 1,
                        'delay_seconds' => $delay,
                    ]);

                    sleep($delay);
                }
            }
        }

        // All attempts failed
        $totalDuration = (microtime(true) - $startTime) * 1000;

        Log::error('Weather API requests exhausted', [
            'lat' => $lat,
            'lon' => $lon,
            'total_attempts' => $this->retryAttempts,
            'total_duration_ms' => round($totalDuration, 2),
            'last_error' => $lastException?->getMessage(),
            'last_error_type' => $lastException ? get_class($lastException) : null,
        ]);

        throw new Exception('Weather service temporarily unavailable. Please try again later.');
    }

    /**
     * Handle specific API error responses
     *
     * @param int $statusCode
     * @param string $responseBody
     * @throws Exception
     */
    private function handleApiError(int $statusCode, string $responseBody): void
    {
        switch ($statusCode) {
            case 401:
                Log::error('Invalid OpenWeather API key', ['response' => $responseBody]);
                throw new Exception('Weather service configuration error');

            case 404:
                Log::warning('Location not found in OpenWeather API', ['response' => $responseBody]);
                throw new Exception('Location not found');

            case 429:
                Log::warning('OpenWeather API rate limit exceeded', ['response' => $responseBody]);
                throw new Exception('Weather service temporarily unavailable due to rate limiting');

            case 500:
            case 502:
            case 503:
            case 504:
                Log::warning('OpenWeather API server error', [
                    'status' => $statusCode,
                    'response' => $responseBody
                ]);
                throw new Exception('Weather service temporarily unavailable');

            default:
                Log::error('Unexpected OpenWeather API error', [
                    'status' => $statusCode,
                    'response' => $responseBody
                ]);
                throw new Exception('Unable to fetch weather data');
        }
    }

    /**
     * Format raw API response into standardized format
     *
     * @param array $apiData
     * @return array
     * @throws Exception
     */
    private function formatWeatherData(array $apiData): array
    {
        try {
            // Validate required fields exist
            $this->validateApiResponse($apiData);

            return [
                'location' => $apiData['name'] ?? 'Unknown Location',
                'temperature' => round($apiData['main']['temp']),
                'condition' => $apiData['weather'][0]['main'] ?? 'Unknown',
                'description' => $apiData['weather'][0]['description'] ?? 'No description',
                'icon' => $apiData['weather'][0]['icon'] ?? '01d',
                'humidity' => $apiData['main']['humidity'] ?? 0,
                'windSpeed' => round($apiData['wind']['speed'] ?? 0, 1),
                'lastUpdated' => now()->toISOString(),
                'coordinates' => [
                    'lat' => $apiData['coord']['lat'] ?? 0,
                    'lon' => $apiData['coord']['lon'] ?? 0,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to format weather data', [
                'error' => $e->getMessage(),
                'data' => $apiData
            ]);
            throw new Exception('Invalid weather data received from service');
        }
    }

    /**
     * Validate API response has required fields
     *
     * @param array $data
     * @throws Exception
     */
    private function validateApiResponse(array $data): void
    {
        $requiredFields = [
            'main.temp',
            'weather.0.main',
            'weather.0.description',
            'weather.0.icon',
            'main.humidity',
        ];

        foreach ($requiredFields as $field) {
            if (!$this->hasNestedKey($data, $field)) {
                throw new Exception("Missing required field: {$field}");
            }
        }
    }

    /**
     * Check if nested key exists in array
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    private function hasNestedKey(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return false;
            }
            $current = $current[$k];
        }

        return true;
    }

    /**
     * Validate coordinates are within valid ranges
     *
     * @param float $lat
     * @param float $lon
     * @throws Exception
     */
    private function validateCoordinates(float $lat, float $lon): void
    {
        if ($lat < -90 || $lat > 90) {
            throw new Exception('Invalid latitude. Must be between -90 and 90.');
        }

        if ($lon < -180 || $lon > 180) {
            throw new Exception('Invalid longitude. Must be between -180 and 180.');
        }
    }

    /**
     * Generate cache key for weather data
     *
     * @param float $lat
     * @param float $lon
     * @return string
     */
    private function generateCacheKey(float $lat, float $lon): string
    {
        return "weather_data_" . number_format($lat, 4, '.', '') . "_" . number_format($lon, 4, '.', '');
    }

    /**
     * Clear cached weather data for specific coordinates
     *
     * @param float $lat
     * @param float $lon
     * @return bool
     */
    public function clearCache(float $lat, float $lon): bool
    {
        $cacheKey = $this->generateCacheKey($lat, $lon);
        return Cache::forget($cacheKey);
    }

    /**
     * Get default location from configuration
     *
     * @return array
     */
    public function getDefaultLocation(): array
    {
        return WeatherConfigService::getDefaultLocation();
    }
}
