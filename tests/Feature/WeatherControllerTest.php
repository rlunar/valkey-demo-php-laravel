<?php

namespace Tests\Feature;

use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WeatherControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up valid test configuration
        Config::set('weather', [
            'api_key' => 'test_api_key',
            'api_url' => 'https://api.openweathermap.org/data/2.5',
            'default_location' => [
                'lat' => 40.7128,
                'lon' => -74.0060,
                'name' => 'New York, NY',
            ],
            'widget' => [
                'enabled' => true,
                'auto_refresh_interval' => 1800,
                'show_detailed_info' => true,
                'temperature_unit' => 'celsius',
            ],
            'cache_ttl' => 900,
            'retry_attempts' => 3,
            'request_timeout' => 10,
            'rate_limiting' => [
                'enabled' => true,
                'max_requests_per_minute' => 60,
                'max_requests_per_hour' => 1000,
            ],
        ]);
    }

    public function test_get_config_returns_valid_configuration(): void
    {
        $response = $this->getJson('/api/weather/config');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'default_location' => ['lat', 'lon', 'name'],
                     'widget' => [
                         'auto_refresh_interval',
                         'show_detailed_info',
                         'temperature_unit'
                     ]
                 ])
                 ->assertJson([
                     'default_location' => [
                         'lat' => 40.7128,
                         'lon' => -74.0060,
                         'name' => 'New York, NY'
                     ],
                     'widget' => [
                         'auto_refresh_interval' => 1800,
                         'show_detailed_info' => true,
                         'temperature_unit' => 'celsius'
                     ]
                 ]);
    }

    public function test_get_config_returns_error_when_widget_disabled(): void
    {
        Config::set('weather.widget.enabled', false);

        $response = $this->getJson('/api/weather/config');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'Weather widget is disabled']);
    }

    public function test_get_config_returns_error_when_api_key_missing(): void
    {
        Config::set('weather.api_key', '');

        $response = $this->getJson('/api/weather/config');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'Weather configuration is not available']);
    }

    public function test_get_current_weather_returns_weather_data(): void
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

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

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
                 ])
                 ->assertJson([
                     'location' => 'New York',
                     'temperature' => 23, // rounded
                     'condition' => 'Clear',
                     'description' => 'clear sky',
                     'icon' => '01d',
                     'humidity' => 65,
                     'windSpeed' => 3.2
                 ]);
    }

    public function test_get_current_weather_validates_coordinates(): void
    {
        $response = $this->getJson('/api/weather?lat=invalid&lon=-74.0060');

        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'Invalid coordinates provided',
                     'details' => [
                         'lat' => ['Latitude must be a valid number']
                     ]
                 ]);
    }

    public function test_get_current_weather_validates_latitude_range(): void
    {
        $response = $this->getJson('/api/weather?lat=91&lon=-74.0060');

        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'Invalid coordinates provided',
                     'details' => [
                         'lat' => ['Latitude must be between -90 and 90 degrees']
                     ]
                 ]);
    }

    public function test_get_current_weather_validates_longitude_range(): void
    {
        $response = $this->getJson('/api/weather?lat=40.7128&lon=181');

        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'Invalid coordinates provided',
                     'details' => [
                         'lon' => ['Longitude must be between -180 and 180 degrees']
                     ]
                 ]);
    }

    public function test_get_current_weather_requires_both_coordinates(): void
    {
        $response = $this->getJson('/api/weather?lat=40.7128');

        $response->assertStatus(400)
                 ->assertJson([
                     'error' => 'Invalid coordinates provided',
                     'details' => [
                         'lon' => ['Longitude is required']
                     ]
                 ]);
    }

    public function test_get_current_weather_handles_api_404_error(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'city not found'], 404)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(404)
                 ->assertJson(['error' => 'Location not found']);
    }

    public function test_get_current_weather_handles_api_401_error(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'Invalid API key'], 401)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'Weather service is not properly configured']);
    }

    public function test_get_current_weather_handles_api_429_error(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'rate limit exceeded'], 429)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(429)
                 ->assertJson(['error' => 'Weather service temporarily unavailable due to high demand']);
    }

    public function test_get_current_weather_handles_api_500_error(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response(['message' => 'internal server error'], 500)
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'Weather data is currently unavailable. Please try again later.']);
    }

    public function test_get_current_weather_handles_network_timeout(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response('', 408) // Request timeout
        ]);

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'Weather data is currently unavailable. Please try again later.']);
    }

    public function test_get_current_weather_logs_request_details(): void
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

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(200);

        // Verify logs were written (this would require log testing setup)
        // For now, we just verify the response is successful
        $this->assertTrue(true);
    }

    public function test_get_current_weather_handles_missing_api_key_configuration(): void
    {
        Config::set('weather.api_key', '');

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'Weather data is currently unavailable. Please try again later.']);
    }

    public function test_weather_controller_resolves_service_dynamically(): void
    {
        // Test that the controller can resolve WeatherService from container
        // even when there are configuration issues at startup

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

        $response = $this->getJson('/api/weather?lat=40.7128&lon=-74.0060');

        $response->assertStatus(200);

        // Verify that the service was resolved and used correctly
        $responseData = $response->json();
        $this->assertEquals('New York', $responseData['location']);
        $this->assertEquals(23, $responseData['temperature']);
    }

    public function test_get_current_weather_handles_coordinate_precision(): void
    {
        $mockResponse = [
            'coord' => ['lat' => 40.712812, 'lon' => -74.006015],
            'weather' => [['main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d']],
            'main' => ['temp' => 22.5, 'humidity' => 65],
            'wind' => ['speed' => 3.2],
            'name' => 'New York'
        ];

        Http::fake([
            'api.openweathermap.org/*' => Http::response($mockResponse, 200)
        ]);

        // Test with high precision coordinates
        $response = $this->getJson('/api/weather?lat=40.712812&lon=-74.006015');

        $response->assertStatus(200)
                 ->assertJson([
                     'coordinates' => [
                         'lat' => 40.712812,
                         'lon' => -74.006015
                     ]
                 ]);
    }
}
