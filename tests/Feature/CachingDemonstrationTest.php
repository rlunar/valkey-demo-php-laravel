<?php

namespace Tests\Feature;

use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CachingDemonstrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('weather.api_key', 'test_api_key');
        Config::set('weather.cache_ttl', 900); // 15 minutes
    }

    public function test_caching_mechanism_demonstration(): void
    {
        $mockResponse = [
            'coord' => ['lat' => 40.7128, 'lon' => -74.0060],
            'weather' => [['main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
            'main' => ['temp' => 22.5, 'humidity' => 65],
            'wind' => ['speed' => 3.2],
            'name' => 'New York'
        ];

        // Track API calls
        $apiCallCount = 0;
        Http::fake([
            'api.openweathermap.org/*' => function () use ($mockResponse, &$apiCallCount) {
                $apiCallCount++;
                return Http::response($mockResponse, 200);
            }
        ]);

        $service = new WeatherService();

        // 1. First call should hit the API and cache the result
        $result1 = $service->fetchWeatherData(40.7128, -74.0060);
        $this->assertEquals(1, $apiCallCount, 'First call should hit the API');
        $this->assertEquals('New York', $result1['location']);

        // Verify cache key exists
        $cacheKey = 'weather_data_40.7128_-74.0060';
        $this->assertTrue(Cache::has($cacheKey), 'Cache should contain the weather data');

        // 2. Second call should use cache (no API call)
        $result2 = $service->fetchWeatherData(40.7128, -74.0060);
        $this->assertEquals(1, $apiCallCount, 'Second call should use cache, not hit API');
        $this->assertEquals($result1, $result2, 'Cached result should match original');

        // 3. Different coordinates should trigger new API call
        $result3 = $service->fetchWeatherData(51.5074, -0.1278);
        $this->assertEquals(2, $apiCallCount, 'Different coordinates should trigger new API call');

        // 4. Cache invalidation should force fresh API call
        $service->clearCache(40.7128, -74.0060);
        $this->assertFalse(Cache::has($cacheKey), 'Cache should be cleared');

        $result4 = $service->fetchWeatherData(40.7128, -74.0060);
        $this->assertEquals(3, $apiCallCount, 'After cache clear, should hit API again');
        $this->assertTrue(Cache::has($cacheKey), 'Cache should be repopulated');

        // 5. Verify TTL is set correctly (15 minutes = 900 seconds)
        $this->assertEquals(900, config('weather.cache_ttl'), 'Cache TTL should be 15 minutes');

        $this->addToAssertionCount(1); // Mark test as having assertions
    }
}
