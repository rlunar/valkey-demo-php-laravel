<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class WeatherWidgetEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test configuration
        Config::set('weather.api_key', 'test_api_key');
        Config::set('weather.default_location', [
            'lat' => 40.7128,
            'lon' => -74.0060,
            'name' => 'New York, NY'
        ]);
        Config::set('weather.cache_ttl', 900); // 15 minutes
        Config::set('weather.retry_attempts', 3);

        // Clear cache before each test
        Cache::flush();
    }

    public function test_completes_full_weather_widget_flow_successfully()
    {
        // Mock successful OpenWeather API response
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'coord' => ['lon' => -74.0060, 'lat' => 40.7128],
                'weather' => [
                    [
                        'id' => 800,
                        'main' => 'Clear',
                        'description' => 'clear sky',
                        'icon' => '01d'
                    ]
                ],
                'main' => [
                    'temp' => 22.5,
                    'feels_like' => 24.0,
                    'humidity' => 65,
                    'pressure' => 1013
                ],
                'wind' => [
                    'speed' => 3.2,
                    'deg' => 180
                ],
                'name' => 'New York',
                'dt' => now()->timestamp
            ], 200)
        ]);

        // Make API request to weather endpoint
        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        // Assert successful response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'location',
                    'temperature',
                    'condition',
                    'description',
                    'icon',
                    'humidity',
                    'windSpeed',
                    'lastUpdated',
                    'coordinates' => ['lat', 'lon']
                ]);

        // Verify response data accuracy
        $data = $response->json();
        $this->assertEquals('New York', $data['location']);
        $this->assertEquals(23, $data['temperature']); // Rounded from 22.5
        $this->assertEquals('Clear', $data['condition']);
        $this->assertEquals('clear sky', $data['description']);
        $this->assertEquals('01d', $data['icon']);
        $this->assertEquals(65, $data['humidity']);
        $this->assertEquals(3.2, $data['windSpeed']);
        $this->assertEquals(40.7128, $data['coordinates']['lat']);
        $this->assertEquals(-74.006, $data['coordinates']['lon']); // Rounded

        // Verify API was called
        Http::assertSentCount(1);
    }

    public function test_handles_missing_coordinates_with_validation_error()
    {
        $response = $this->getJson('/api/weather');

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Invalid coordinates provided'
                ])
                ->assertJsonStructure([
                    'error',
                    'details' => [
                        'lat',
                        'lon'
                    ]
                ]);
    }

    public function test_handles_invalid_coordinates_with_validation_error()
    {
        $response = $this->getJson('/api/weather?lat=invalid&lon=invalid');

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Invalid coordinates provided'
                ])
                ->assertJsonStructure([
                    'error',
                    'details' => [
                        'lat',
                        'lon'
                    ]
                ]);
    }

    public function test_handles_out_of_range_coordinates_with_validation_error()
    {
        $response = $this->getJson('/api/weather?lat=91&lon=181'); // Invalid coordinates

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Invalid coordinates provided'
                ])
                ->assertJsonStructure([
                    'error',
                    'details' => [
                        'lat',
                        'lon'
                    ]
                ]);
    }

    public function test_caches_weather_data_correctly()
    {
        // Mock successful API response
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'coord' => ['lon' => -74.0060, 'lat' => 40.7128],
                'weather' => [['id' => 800, 'main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
                'main' => ['temp' => 22.5, 'feels_like' => 24.0, 'humidity' => 65, 'pressure' => 1013],
                'wind' => ['speed' => 3.2, 'deg' => 180],
                'name' => 'New York',
                'dt' => now()->timestamp
            ], 200)
        ]);

        $url = '/api/weather?lat=40.7128&lon=-74.0060';

        // First request - should hit API
        $response1 = $this->getJson($url);
        $response1->assertStatus(200);

        // Second request - should use cache
        $response2 = $this->getJson($url);
        $response2->assertStatus(200);

        // Verify API was only called once
        Http::assertSentCount(1);

        // Verify both responses are identical
        $this->assertEquals($response1->json(), $response2->json());

        // Verify cache key exists
        $cacheKey = 'weather_' . md5('40.7128,-74.0060');
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_handles_openweather_api_errors_gracefully()
    {
        // Mock API error response
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'cod' => 401,
                'message' => 'Invalid API key'
            ], 401)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                ->assertJson([
                    'error' => 'Weather service is not properly configured'
                ]);
    }

    public function test_handles_network_timeout_errors()
    {
        // Mock network timeout
        Http::fake([
            'api.openweathermap.org/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            }
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                ->assertJson([
                    'error' => 'Weather data is currently unavailable. Please try again later.'
                ]);
    }

    public function test_handles_rate_limiting_from_openweather_api()
    {
        // Mock rate limit response
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'cod' => 429,
                'message' => 'Your account is temporary blocked due to exceeding of requests limitation'
            ], 429)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(429)
                ->assertJson([
                    'error' => 'Weather service temporarily unavailable due to high demand'
                ]);
    }

    public function test_handles_location_not_found_errors()
    {
        // Mock location not found response
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'cod' => '404',
                'message' => 'city not found'
            ], 404)
        ]);

        $response = $this->getJson('/api/weather?lat=999&lon=999');

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Invalid coordinates provided'
                ]);
    }

    public function test_validates_weather_service_configuration()
    {
        // Test with missing API key
        Config::set('weather.api_key', null);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                ->assertJson([
                    'error' => 'Weather service is not properly configured'
                ]);
    }

    public function test_returns_proper_weather_data_format()
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'coord' => ['lon' => -74.0060, 'lat' => 40.7128],
                'weather' => [
                    [
                        'id' => 800,
                        'main' => 'Clear',
                        'description' => 'clear sky',
                        'icon' => '01d'
                    ]
                ],
                'main' => [
                    'temp' => 22.5,
                    'feels_like' => 24.0,
                    'humidity' => 65,
                    'pressure' => 1013
                ],
                'wind' => [
                    'speed' => 3.2,
                    'deg' => 180
                ],
                'name' => 'New York',
                'dt' => now()->timestamp
            ], 200)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $data = $response->json();

        // Verify all required fields are present
        $this->assertArrayHasKey('location', $data);
        $this->assertArrayHasKey('temperature', $data);
        $this->assertArrayHasKey('condition', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('icon', $data);
        $this->assertArrayHasKey('humidity', $data);
        $this->assertArrayHasKey('windSpeed', $data);
        $this->assertArrayHasKey('lastUpdated', $data);
        $this->assertArrayHasKey('coordinates', $data);

        // Verify data types
        $this->assertIsString($data['location']);
        $this->assertIsNumeric($data['temperature']);
        $this->assertIsString($data['condition']);
        $this->assertIsString($data['description']);
        $this->assertIsString($data['icon']);
        $this->assertIsInt($data['humidity']);
        $this->assertIsNumeric($data['windSpeed']);
        $this->assertIsString($data['lastUpdated']);
        $this->assertIsArray($data['coordinates']);

        // Verify coordinate structure
        $this->assertArrayHasKey('lat', $data['coordinates']);
        $this->assertArrayHasKey('lon', $data['coordinates']);
        $this->assertIsNumeric($data['coordinates']['lat']);
        $this->assertIsNumeric($data['coordinates']['lon']);

        // Verify timestamp format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['lastUpdated']);
    }

    public function test_handles_malformed_api_responses()
    {
        // Mock malformed API response
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'invalid' => 'response'
            ], 200)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                ->assertJson([
                    'error' => 'Weather data is currently unavailable. Please try again later.'
                ]);
    }

    public function test_handles_concurrent_requests_correctly()
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'coord' => ['lon' => -74.0060, 'lat' => 40.7128],
                'weather' => [['id' => 800, 'main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
                'main' => ['temp' => 22.5, 'feels_like' => 24.0, 'humidity' => 65, 'pressure' => 1013],
                'wind' => ['speed' => 3.2, 'deg' => 180],
                'name' => 'New York',
                'dt' => now()->timestamp
            ], 200)
        ]);

        $url = '/api/weather?lat=40.7128&lon=-74.0060';

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson($url);
        }

        // All requests should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // API should only be called once due to caching
        Http::assertSentCount(1);
    }

    public function test_respects_cache_ttl_configuration()
    {
        // Set short cache TTL for testing
        Config::set('weather.cache_ttl', 1); // 1 second

        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'coord' => ['lon' => -74.0060, 'lat' => 40.7128],
                'weather' => [['id' => 800, 'main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
                'main' => ['temp' => 22.5, 'feels_like' => 24.0, 'humidity' => 65, 'pressure' => 1013],
                'wind' => ['speed' => 3.2, 'deg' => 180],
                'name' => 'New York',
                'dt' => now()->timestamp
            ], 200)
        ]);

        $url = '/api/weather?lat=40.7128&lon=-74.0060';

        // First request
        $response1 = $this->getJson($url);
        $response1->assertStatus(200);

        // Wait for cache to expire
        sleep(2);

        // Second request should hit API again
        $response2 = $this->getJson($url);
        $response2->assertStatus(200);

        // API should be called twice
        Http::assertSentCount(2);
    }

    public function test_handles_different_coordinate_formats()
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'coord' => ['lon' => -74.0060, 'lat' => 40.7128],
                'weather' => [['id' => 800, 'main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
                'main' => ['temp' => 22.5, 'feels_like' => 24.0, 'humidity' => 65, 'pressure' => 1013],
                'wind' => ['speed' => 3.2, 'deg' => 180],
                'name' => 'New York',
                'dt' => now()->timestamp
            ], 200)
        ]);

        // Test integer coordinates
        $response1 = $this->getJson('/api/weather?lat=40&lon=-74');
        $response1->assertStatus(200);

        // Test float coordinates
        $response2 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');
        $response2->assertStatus(200);

        // Test string coordinates (should be converted)
        $response3 = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');
        $response3->assertStatus(200);

        Http::assertSentCount(3);
    }
}
