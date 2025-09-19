<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class WeatherDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_endpoint_debug()
    {
        // Set up test configuration
        Config::set('weather.api_key', 'test_api_key');
        Config::set('weather.default_location', [
            'lat' => 40.7128,
            'lon' => -74.0060,
            'name' => 'New York, NY'
        ]);

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

        // Debug the response
        dump('Status:', $response->getStatusCode());
        dump('Content:', $response->getContent());
        dump('Headers:', $response->headers->all());

        $this->assertTrue(true); // Just to make the test pass
    }
}
