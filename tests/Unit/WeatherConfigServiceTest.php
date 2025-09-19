<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WeatherConfigService;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class WeatherConfigServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up valid default configuration for tests
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

    public function test_get_validated_config_returns_valid_configuration()
    {
        $config = WeatherConfigService::getValidatedConfig();

        $this->assertIsArray($config);
        $this->assertEquals('test_api_key', $config['api_key']);
        $this->assertEquals('https://api.openweathermap.org/data/2.5', $config['api_url']);
        $this->assertEquals(40.7128, $config['default_location']['lat']);
        $this->assertEquals(-74.0060, $config['default_location']['lon']);
        $this->assertEquals('New York, NY', $config['default_location']['name']);
    }

    public function test_get_validated_config_throws_exception_for_missing_api_key()
    {
        Config::set('weather.api_key', '');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenWeather API key is required but not configured');

        WeatherConfigService::getValidatedConfig();
    }

    public function test_get_validated_config_corrects_invalid_coordinates()
    {
        Config::set('weather.default_location.lat', 100); // Invalid latitude
        Config::set('weather.default_location.lon', 200); // Invalid longitude

        $config = WeatherConfigService::getValidatedConfig();

        // Should fall back to New York coordinates
        $this->assertEquals(40.7128, $config['default_location']['lat']);
        $this->assertEquals(-74.0060, $config['default_location']['lon']);
    }

    public function test_get_validated_config_corrects_invalid_refresh_interval()
    {
        Config::set('weather.widget.auto_refresh_interval', 100); // Too short

        $config = WeatherConfigService::getValidatedConfig();

        // Should use minimum of 5 minutes (300 seconds)
        $this->assertEquals(300, $config['widget']['auto_refresh_interval']);
    }

    public function test_get_validated_config_corrects_invalid_temperature_unit()
    {
        Config::set('weather.widget.temperature_unit', 'kelvin'); // Invalid unit

        $config = WeatherConfigService::getValidatedConfig();

        // Should fall back to celsius
        $this->assertEquals('celsius', $config['widget']['temperature_unit']);
    }

    public function test_get_validated_config_corrects_invalid_cache_ttl()
    {
        Config::set('weather.cache_ttl', 30); // Too short

        $config = WeatherConfigService::getValidatedConfig();

        // Should use minimum of 1 minute (60 seconds)
        $this->assertEquals(60, $config['cache_ttl']);
    }

    public function test_get_validated_config_corrects_invalid_retry_attempts()
    {
        Config::set('weather.retry_attempts', 15); // Too many

        $config = WeatherConfigService::getValidatedConfig();

        // Should fall back to default 3
        $this->assertEquals(3, $config['retry_attempts']);
    }

    public function test_get_default_location_returns_correct_data()
    {
        $location = WeatherConfigService::getDefaultLocation();

        $this->assertIsArray($location);
        $this->assertEquals(40.7128, $location['lat']);
        $this->assertEquals(-74.0060, $location['lon']);
        $this->assertEquals('New York, NY', $location['name']);
    }

    public function test_get_widget_config_returns_correct_data()
    {
        $widgetConfig = WeatherConfigService::getWidgetConfig();

        $this->assertIsArray($widgetConfig);
        $this->assertTrue($widgetConfig['enabled']);
        $this->assertEquals(1800, $widgetConfig['auto_refresh_interval']);
        $this->assertTrue($widgetConfig['show_detailed_info']);
        $this->assertEquals('celsius', $widgetConfig['temperature_unit']);
    }

    public function test_is_widget_enabled_returns_true_for_enabled_widget()
    {
        $this->assertTrue(WeatherConfigService::isWidgetEnabled());
    }

    public function test_is_widget_enabled_returns_false_for_disabled_widget()
    {
        Config::set('weather.widget.enabled', false);

        $this->assertFalse(WeatherConfigService::isWidgetEnabled());
    }

    public function test_is_widget_enabled_returns_false_for_invalid_config()
    {
        Config::set('weather.api_key', ''); // Invalid config

        $this->assertFalse(WeatherConfigService::isWidgetEnabled());
    }

    public function test_get_api_config_returns_correct_data()
    {
        $apiConfig = WeatherConfigService::getApiConfig();

        $this->assertIsArray($apiConfig);
        $this->assertEquals('test_api_key', $apiConfig['api_key']);
        $this->assertEquals('https://api.openweathermap.org/data/2.5', $apiConfig['api_url']);
        $this->assertEquals(10, $apiConfig['request_timeout']);
    }

    public function test_get_cache_config_returns_correct_data()
    {
        $cacheConfig = WeatherConfigService::getCacheConfig();

        $this->assertIsArray($cacheConfig);
        $this->assertEquals(900, $cacheConfig['cache_ttl']);
        $this->assertEquals(3, $cacheConfig['retry_attempts']);
    }

    public function test_get_rate_limiting_config_returns_correct_data()
    {
        $rateLimitingConfig = WeatherConfigService::getRateLimitingConfig();

        $this->assertIsArray($rateLimitingConfig);
        $this->assertTrue($rateLimitingConfig['enabled']);
        $this->assertEquals(60, $rateLimitingConfig['max_requests_per_minute']);
        $this->assertEquals(1000, $rateLimitingConfig['max_requests_per_hour']);
    }
}
