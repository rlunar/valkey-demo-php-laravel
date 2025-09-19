<?php

namespace Tests\Unit;

use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Exception;

class WeatherServiceTest extends TestCase
{
    use RefreshDatabase;

    private WeatherService $weatherService;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test configuration
        Config::set('weather.api_key', 'test_api_key');
        Config::set('weather.api_url', 'https://api.openweathermap.org/data/2.5');
        Config::set('weather.cache_ttl', 900);
        Config::set('weather.retry_attempts', 3);

        $this->weatherService = new WeatherService();
    }

    public function test_constructor_throws_exception_when_api_key_missing(): void
    {
        Config::set('weather.api_key', '');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('OpenWeather API key is required but not configured');

        new WeatherService();
    }

    public function test_fetch_weather_data_returns_formatted_data(): void
    {
        $mockResponse = [
            'coord' => ['lat' => 40.7128, 'lon' => -74.0060],
            'weather' => [
                [
                    'main' => 'Clear',
                    'description' => 'clear sky',
                    'icon' => '01d'
                ]
            ],
            'main' => [
                'temp' => 22.5,
                'humidity' => 65
            ],
            'wind' => [
                'speed' => 3.2
            ],
            'name' => 'New York'
        ];

        Http::fake([
            'api.openweathermap.org/*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->weatherService->fetchWeatherData(40.7128, -74.0060);

        $this->assertIsArray($result);
        $this->assertEquals('New York', $result['location']);
        $this->assertEquals(23, $result['temperature']); // rounded
        $this->assertEquals('Clear', $result['condition']);
        $this->assertEquals('clear sky', $result['description']);
        $this->assertEquals('01d', $result['icon']);
        $this->assertEquals(65, $result['humidity']);
        $this->assertEquals(3.2, $result['windSpeed']);
        $this->assertArrayHasKey('lastUpdated', $result);
        $this->assertArrayHasKey('coordinates', $result);
    }

    public function test_fetch_weather_data_uses_cache(): void
    {
        $cachedData = [
            'location' => 'Cached Location',
            'temperature' => 20,
            'condition' => 'Cached',
            'description' => 'cached data',
            'icon' => '02d',
            'humidity' => 50,
            'windSpeed' => 2.0,
            'lastUpdated' => '2023-01-01T00:00:00.000000Z',
            'coordinates' => ['lat' => 40.7128, 'lon' => -74.0060]
        ];

        Cache::put('weather_data_40.7128_-74.0060', $cachedData, 900);

        // Should not make HTTP request since data is cached
        Http::fake();

        $result = $this->weatherService->fetchWeatherData(40.7128, -74.0060);

        $this->assertEquals($cachedData, $result);
        Http::assertNothingSent();
    }

    public function test_validate_coordinates_throws_exception_for_invalid_latitude(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid latitude. Must be between -90 and 90.');

        $this->weatherService->fetchWeatherData(91, 0);
    }

    public function test_validate_coordinates_throws_exception_for_invalid_longitude(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid longitude. Must be between -180 and 180.');

        $this->weatherService->fetchWeatherData(0, 181);
    }

    public function test_handles_api_error_401_unauthorized(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Invalid API key'], 401)
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Weather service configuration error');

        $this->weatherService->fetchWeatherData(40.7128, -74.0060);
    }

    public function test_handles_api_error_404_not_found(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'city not found'], 404)
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Location not found');

        $this->weatherService->fetchWeatherData(40.7128, -74.0060);
    }

    public function test_handles_api_error_429_rate_limit(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'rate limit exceeded'], 429)
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Weather service temporarily unavailable due to rate limiting');

        $this->weatherService->fetchWeatherData(40.7128, -74.0060);
    }

    public function test_handles_api_error_500_server_error(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'internal server error'], 500)
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Weather service temporarily unavailable');

        $this->weatherService->fetchWeatherData(40.7128, -74.0060);
    }

    public function test_retries_on_network_failure(): void
    {
        // First two calls fail, third succeeds
        Http::fake([
            'api.openweathermap.org/*' => Http::sequence()
                ->push(null, 500) // First call fails
                ->push(null, 500) // Second call fails
                ->push([
                    'coord' => ['lat' => 40.7128, 'lon' => -74.0060],
                    'weather' => [['main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
                    'main' => ['temp' => 22.5, 'humidity' => 65],
                    'wind' => ['speed' => 3.2],
                    'name' => 'New York'
                ], 200) // Third call succeeds
        ]);

        $result = $this->weatherService->fetchWeatherData(40.7128, -74.0060);

        $this->assertIsArray($result);
        $this->assertEquals('New York', $result['location']);
    }

    public function test_format_weather_data_handles_missing_optional_fields(): void
    {
        $minimalResponse = [
            'coord' => ['lat' => 40.7128, 'lon' => -74.0060],
            'weather' => [
                [
                    'main' => 'Clear',
                    'description' => 'clear sky',
                    'icon' => '01d'
                ]
            ],
            'main' => [
                'temp' => 22.5,
                'humidity' => 65
            ],
            // Missing wind data and name
        ];

        Http::fake([
            'api.openweathermap.org/*' => Http::response($minimalResponse, 200)
        ]);

        $result = $this->weatherService->fetchWeatherData(40.7128, -74.0060);

        $this->assertEquals('Unknown Location', $result['location']);
        $this->assertEquals(0, $result['windSpeed']);
    }

    public function test_clear_cache_removes_cached_data(): void
    {
        $cacheKey = 'weather_data_40.7128_-74.0060';
        Cache::put($cacheKey, ['test' => 'data'], 900);

        $this->assertTrue(Cache::has($cacheKey));

        $result = $this->weatherService->clearCache(40.7128, -74.0060);

        $this->assertTrue($result);
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_get_default_location_returns_config_value(): void
    {
        $defaultLocation = [
            'lat' => 40.7128,
            'lon' => -74.0060,
            'name' => 'New York, NY'
        ];

        Config::set('weather.default_location', $defaultLocation);

        $result = $this->weatherService->getDefaultLocation();

        $this->assertEquals($defaultLocation, $result);
    }

    public function test_generate_cache_key_rounds_coordinates(): void
    {
        // Test that coordinates are properly rounded for cache key generation
        $reflection = new \ReflectionClass($this->weatherService);
        $method = $reflection->getMethod('generateCacheKey');
        $method->setAccessible(true);

        $result = $method->invoke($this->weatherService, 40.712812, -74.006015);

        $this->assertEquals('weather_data_40.7128_-74.0060', $result);
    }
}
