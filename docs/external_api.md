# Weather API Caching Implementation

## Overview
This document shows how to add lazy loading cache to the WeatherController's `getRandomWeather` and `getMultipleRandomWeather` methods to reduce latency and improve performance.

## Implementation

Update your `WeatherController` with the following cached versions of the methods:

### Updated getRandomWeather Method

```php
use Illuminate\Support\Facades\Cache;

/**
 * Get weather data for a random city with caching (mocked OpenWeatherMap response)
 */
public function getRandomWeather(): JsonResponse
{
    return Cache::remember('weather_random_' . date('Y-m-d-H-i'), 1800, function () {
        // Simulate external API latency (250-750ms)
        $latency = rand(250, 750);
        usleep($latency * 1000); // Convert to microseconds

        $city = WeatherCity::getRandomCity();

        if (! $city) {
            return response()->json(['error' => 'No cities available'], 404);
        }

        // Mock weather data similar to OpenWeatherMap API
        $weatherData = $this->generateMockWeatherData($city);
        $weatherData['cached_at'] = now()->toISOString();

        return response()->json($weatherData);
    });
}
```

### Updated getMultipleRandomWeather Method

```php
/**
 * Get weather data for multiple random cities with caching
 */
public function getMultipleRandomWeather(Request $request): JsonResponse
{
    $count = min($request->get('count', 5), 10); // Limit to 10 cities max
    
    return Cache::remember("weather_multiple_{$count}_" . date('Y-m-d-H-i'), 1800, function () use ($count) {
        // Simulate external API latency (250-750ms)
        $latency = rand(250, 750);
        usleep($latency * 1000); // Convert to microseconds

        $cities = WeatherCity::getRandomCities($count);

        if ($cities->isEmpty()) {
            return response()->json(['error' => 'No cities available'], 404);
        }

        $weatherData = $cities->map(function ($city) {
            $data = $this->generateMockWeatherData($city);
            $data['cached_at'] = now()->toISOString();
            return $data;
        });

        return response()->json($weatherData);
    });
}
```

## Cache Configuration

### TTL Settings
- Default TTL: 30 minutes (1800 seconds)
- Cache keys include timestamp to ensure fresh data every minute
- JSON responses are automatically serialized by Laravel's cache

### Extending/Reducing TTL

To customize cache duration, modify the second parameter in `Cache::remember()`:

```php
// 15 minutes cache
Cache::remember($key, 900, $callback);

// 1 hour cache  
Cache::remember($key, 3600, $callback);

// 5 minutes cache
Cache::remember($key, 300, $callback);
```

### Environment Configuration

Add to your `.env` file:

```env
# Cache driver (file, redis, memcached)
CACHE_DRIVER=file

# For production, use Redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Benefits

- **Reduced Latency**: Subsequent requests return cached data instantly
- **Lower Resource Usage**: Eliminates redundant processing and API simulation
- **Better User Experience**: Faster response times for repeated requests
- **Automatic Serialization**: Laravel handles JSON serialization automatically

The cache will automatically expire after 30 minutes, ensuring data freshness while providing performance benefits for frequently accessed endpoints.