<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WeatherConfigService
{
    /**
     * Validate weather configuration and return validated config
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getValidatedConfig(): array
    {
        $config = Config::get('weather');

        // Validate API configuration
        if (empty($config['api_key'])) {
            throw new InvalidArgumentException('OpenWeather API key is required but not configured');
        }

        if (empty($config['api_url'])) {
            throw new InvalidArgumentException('OpenWeather API URL is required but not configured');
        }

        // Validate default location
        $defaultLocation = $config['default_location'];

        if (!is_numeric($defaultLocation['lat']) || $defaultLocation['lat'] < -90 || $defaultLocation['lat'] > 90) {
            Log::warning('Invalid default latitude in weather config, using fallback', [
                'configured_lat' => $defaultLocation['lat']
            ]);
            $config['default_location']['lat'] = 40.7128; // New York fallback
        }

        if (!is_numeric($defaultLocation['lon']) || $defaultLocation['lon'] < -180 || $defaultLocation['lon'] > 180) {
            Log::warning('Invalid default longitude in weather config, using fallback', [
                'configured_lon' => $defaultLocation['lon']
            ]);
            $config['default_location']['lon'] = -74.0060; // New York fallback
        }

        if (empty($defaultLocation['name'])) {
            Log::warning('Empty default location name in weather config, using fallback');
            $config['default_location']['name'] = 'New York, NY';
        }

        // Validate widget configuration
        if (!isset($config['widget']['enabled'])) {
            $config['widget']['enabled'] = true;
        }

        if (!is_numeric($config['widget']['auto_refresh_interval']) || $config['widget']['auto_refresh_interval'] < 300) {
            Log::warning('Invalid auto refresh interval in weather config, using minimum 5 minutes', [
                'configured_interval' => $config['widget']['auto_refresh_interval']
            ]);
            $config['widget']['auto_refresh_interval'] = 300; // 5 minutes minimum
        }

        if (!in_array($config['widget']['temperature_unit'], ['celsius', 'fahrenheit'])) {
            Log::warning('Invalid temperature unit in weather config, using celsius', [
                'configured_unit' => $config['widget']['temperature_unit']
            ]);
            $config['widget']['temperature_unit'] = 'celsius';
        }

        // Validate cache configuration
        if (!is_numeric($config['cache_ttl']) || $config['cache_ttl'] < 60) {
            Log::warning('Invalid cache TTL in weather config, using minimum 1 minute', [
                'configured_ttl' => $config['cache_ttl']
            ]);
            $config['cache_ttl'] = 60; // 1 minute minimum
        }

        if (!is_numeric($config['retry_attempts']) || $config['retry_attempts'] < 1 || $config['retry_attempts'] > 10) {
            Log::warning('Invalid retry attempts in weather config, using default 3', [
                'configured_attempts' => $config['retry_attempts']
            ]);
            $config['retry_attempts'] = 3;
        }

        if (!is_numeric($config['request_timeout']) || $config['request_timeout'] < 1 || $config['request_timeout'] > 60) {
            Log::warning('Invalid request timeout in weather config, using default 10 seconds', [
                'configured_timeout' => $config['request_timeout']
            ]);
            $config['request_timeout'] = 10;
        }

        // Validate rate limiting configuration
        if (!isset($config['rate_limiting']['enabled'])) {
            $config['rate_limiting']['enabled'] = true;
        }

        if (!is_numeric($config['rate_limiting']['max_requests_per_minute']) || $config['rate_limiting']['max_requests_per_minute'] < 1) {
            Log::warning('Invalid max requests per minute in weather config, using default 60', [
                'configured_max' => $config['rate_limiting']['max_requests_per_minute']
            ]);
            $config['rate_limiting']['max_requests_per_minute'] = 60;
        }

        if (!is_numeric($config['rate_limiting']['max_requests_per_hour']) || $config['rate_limiting']['max_requests_per_hour'] < 1) {
            Log::warning('Invalid max requests per hour in weather config, using default 1000', [
                'configured_max' => $config['rate_limiting']['max_requests_per_hour']
            ]);
            $config['rate_limiting']['max_requests_per_hour'] = 1000;
        }

        return $config;
    }

    /**
     * Get default location configuration
     *
     * @return array
     */
    public static function getDefaultLocation(): array
    {
        $config = self::getValidatedConfig();
        return $config['default_location'];
    }

    /**
     * Get widget configuration
     *
     * @return array
     */
    public static function getWidgetConfig(): array
    {
        $config = self::getValidatedConfig();
        return $config['widget'];
    }

    /**
     * Check if weather widget is enabled
     *
     * @return bool
     */
    public static function isWidgetEnabled(): bool
    {
        try {
            $config = self::getValidatedConfig();
            return (bool) $config['widget']['enabled'];
        } catch (InvalidArgumentException $e) {
            Log::error('Weather widget disabled due to configuration error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get API configuration
     *
     * @return array
     */
    public static function getApiConfig(): array
    {
        $config = self::getValidatedConfig();
        return [
            'api_key' => $config['api_key'],
            'api_url' => $config['api_url'],
            'request_timeout' => $config['request_timeout'],
        ];
    }

    /**
     * Get cache configuration
     *
     * @return array
     */
    public static function getCacheConfig(): array
    {
        $config = self::getValidatedConfig();
        return [
            'cache_ttl' => $config['cache_ttl'],
            'retry_attempts' => $config['retry_attempts'],
        ];
    }

    /**
     * Get rate limiting configuration
     *
     * @return array
     */
    public static function getRateLimitingConfig(): array
    {
        $config = self::getValidatedConfig();
        return $config['rate_limiting'];
    }
}
