<?php

namespace Tests\Feature;

use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WeatherControllerCachingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('weather.api_key', 'test_api_key');
        Config::set('weather.api_url', 'https://api.openweathermap.org/data/2.5');
        Config::set('weather.cache_ttl', 900); // 15 minutes
        Config::set('weather.retry_attempts', 3);
    }

    public function test_weather_controller_uses_cached_data(): void
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

        // First request should hit the API and cache the result
        $response1 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response1->assertStatus(200)
                  ->assertJson([
                      'location' => 'New York',
                      'temperature' => 23,
                      'condition' => 'Clear'
                  ]);

        // Verify cache was populated
        $cacheKey = 'weather_data_40.7128_-74.0060';
        $this->assertTrue(Cache::has($cacheKey));

        // Reset HTTP fake to ensure no new requests are made
        Http::fake();

        // Second request should use cached data
        $response2 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response2->assertStatus(200)
                  ->assertJson([
                      'location' => 'New York',
                      'temperature' => 23,
                      'condition' => 'Clear'
                  ]);

        // Verify no HTTP requests were made (used cache)
        Http::assertNothingSent();
    }

    public function test_different_coordinates_use_separate_cache(): void
    {
        $mockResponse1 = [
            'coord' => ['lat' => 40.7128, 'lon' => -74.0060],
            'weather' => [['main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
            'main' => ['temp' => 22.5, 'humidity' => 65],
            'wind' => ['speed' => 3.2],
            'name' => 'New York'
        ];

        $mockResponse2 = [
            'coord' => ['lat' => 51.5074, 'lon' => -0.1278],
            'weather' => [['main' => 'Clouds', 'description' => 'overcast clouds', 'icon' => '04d']],
            'main' => ['temp' => 15.0, 'humidity' => 80],
            'wind' => ['speed' => 2.1],
            'name' => 'London'
        ];

        Http::fake([
            'api.openweathermap.org/*' => Http::sequence()
                ->push($mockResponse1, 200)
                ->push($mockResponse2, 200)
        ]);

        // Request weather for New York
        $response1 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');
        $response1->assertStatus(200)
                  ->assertJson(['location' => 'New York']);

        // Request weather for London
        $response2 = $this->getJson('/api/weather?lat=51.5074&lon=-0.1278');
        $response2->assertStatus(200)
                  ->assertJson(['location' => 'London']);

        // Verify both cache entries exist
        $this->assertTrue(Cache::has('weather_data_40.7128_-74.0060'));
        $this->assertTrue(Cache::has('weather_data_51.5074_-0.1278'));

        // Reset HTTP fake
        Http::fake();

        // Subsequent requests should use cache
        $response3 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');
        $response4 = $this->getJson('/api/weather?lat=51.5074&lon=-0.1278');

        $response3->assertStatus(200)->assertJson(['location' => 'New York']);
        $response4->assertStatus(200)->assertJson(['location' => 'London']);

        // No HTTP requests should have been made
        Http::assertNothingSent();
    }

    public function test_cache_invalidation_forces_fresh_api_call(): void
    {
        $mockResponse = [
            'coord' => ['lat' => 40.7128, 'lon' => -74.0060],
            'weather' => [['main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
            'main' => ['temp' => 22.5, 'humidity' => 65],
            'wind' => ['speed' => 3.2],
            'name' => 'New York'
        ];

        Http::fake([
            'api.openweathermap.org/*' => Http::response($mockResponse, 200)
        ]);

        // First request populates cache
        $response1 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');
        $response1->assertStatus(200);

        // Verify cache exists
        $cacheKey = 'weather_data_40.7128_-74.0060';
        $this->assertTrue(Cache::has($cacheKey));

        // Clear the cache manually (simulating cache invalidation)
        $weatherService = app(WeatherService::class);
        $weatherService->clearCache(40.7128, -74.0060);
        $this->assertFalse(Cache::has($cacheKey));

        // Next request should hit the API again
        $response2 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');
        $response2->assertStatus(200);

        // Verify cache was repopulated
        $this->assertTrue(Cache::has($cacheKey));
    }
}
