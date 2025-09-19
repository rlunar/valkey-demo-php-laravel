<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class WeatherConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up valid configuration for tests
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

    public function test_weather_config_endpoint_returns_valid_configuration()
    {
        $response = $this->get('/api/weather/config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'default_location' => [
                    'lat',
                    'lon',
                    'name',
                ],
                'widget' => [
                    'auto_refresh_interval',
                    'show_detailed_info',
                    'temperature_unit',
                ],
            ])
            ->assertJson([
                'default_location' => [
                    'lat' => 40.7128,
                    'lon' => -74.0060,
                    'name' => 'New York, NY',
                ],
                'widget' => [
                    'auto_refresh_interval' => 1800,
                    'show_detailed_info' => true,
                    'temperature_unit' => 'celsius',
                ],
            ]);
    }

    public function test_weather_config_endpoint_returns_error_when_widget_disabled()
    {
        Config::set('weather.widget.enabled', false);

        $response = $this->get('/api/weather/config');

        $response->assertStatus(503)
            ->assertJson([
                'error' => 'Weather widget is disabled'
            ]);
    }

    public function test_weather_config_endpoint_returns_error_when_api_key_missing()
    {
        Config::set('weather.api_key', '');

        $response = $this->get('/api/weather/config');

        $response->assertStatus(503)
            ->assertJson([
                'error' => 'Weather configuration is not available'
            ]);
    }

    public function test_weather_config_endpoint_handles_invalid_coordinates_gracefully()
    {
        Config::set('weather.default_location.lat', 100); // Invalid latitude
        Config::set('weather.default_location.lon', 200); // Invalid longitude

        $response = $this->get('/api/weather/config');

        $response->assertStatus(200);

        $data = $response->json();

        // Should fall back to valid coordinates
        $this->assertEquals(40.7128, $data['default_location']['lat']);
        $this->assertEquals(-74.0060, $data['default_location']['lon']);
    }

    public function test_weather_config_endpoint_corrects_invalid_refresh_interval()
    {
        Config::set('weather.widget.auto_refresh_interval', 100); // Too short

        $response = $this->get('/api/weather/config');

        $response->assertStatus(200);

        $data = $response->json();

        // Should use minimum of 5 minutes (300 seconds)
        $this->assertEquals(300, $data['widget']['auto_refresh_interval']);
    }

    public function test_weather_config_endpoint_corrects_invalid_temperature_unit()
    {
        Config::set('weather.widget.temperature_unit', 'kelvin'); // Invalid unit

        $response = $this->get('/api/weather/config');

        $response->assertStatus(200);

        $data = $response->json();

        // Should fall back to celsius
        $this->assertEquals('celsius', $data['widget']['temperature_unit']);
    }
}
