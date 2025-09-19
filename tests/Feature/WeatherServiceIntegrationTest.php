<?php

namespace Tests\Feature;

use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Exception;

class WeatherServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_service_can_be_instantiated_from_container(): void
    {
        Config::set('weather.api_key', 'test_api_key');

        $service = app(WeatherService::class);

        $this->assertInstanceOf(WeatherService::class, $service);
    }

    public function test_weather_service_integration_with_mock_api(): void
    {
        Config::set('weather.api_key', 'test_api_key');

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

        $service = app(WeatherService::class);
        $result = $service->fetchWeatherData(40.7128, -74.0060);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('location', $result);
        $this->assertArrayHasKey('temperature', $result);
        $this->assertArrayHasKey('condition', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('icon', $result);
        $this->assertArrayHasKey('humidity', $result);
        $this->assertArrayHasKey('windSpeed', $result);
        $this->assertArrayHasKey('lastUpdated', $result);
        $this->assertArrayHasKey('coordinates', $result);

        $this->assertEquals('New York', $result['location']);
        $this->assertEquals(23, $result['temperature']);
        $this->assertEquals('Clear', $result['condition']);
    }

    public function test_weather_service_uses_configuration_correctly(): void
    {
        Config::set('weather.api_key', 'test_api_key');
        Config::set('weather.default_location', [
            'lat' => 51.5074,
            'lon' => -0.1278,
            'name' => 'London, UK'
        ]);

        $service = app(WeatherService::class);
        $defaultLocation = $service->getDefaultLocation();

        $this->assertEquals([
            'lat' => 51.5074,
            'lon' => -0.1278,
            'name' => 'London, UK'
        ], $defaultLocation);
    }

    public function test_weather_service_handles_missing_api_key_gracefully(): void
    {
        Config::set('weather.api_key', '');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('OpenWeather API key is not configured');

        app(WeatherService::class);
    }
}
