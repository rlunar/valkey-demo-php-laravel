<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenWeather API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenWeather API integration
    |
    */

    'api_key' => env('OPENWEATHER_API_KEY'),
    'api_url' => env('OPENWEATHER_API_URL', 'https://api.openweathermap.org/data/2.5'),

    /*
    |--------------------------------------------------------------------------
    | Default Location Configuration
    |--------------------------------------------------------------------------
    |
    | Default location to use when user's geolocation is not available
    | Coordinates must be valid latitude (-90 to 90) and longitude (-180 to 180)
    |
    */

    'default_location' => [
        'lat' => (float) env('WEATHER_DEFAULT_LAT', 40.7128),
        'lon' => (float) env('WEATHER_DEFAULT_LON', -74.0060),
        'name' => env('WEATHER_DEFAULT_LOCATION', 'New York, NY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for weather widget behavior and display
    |
    */

    'widget' => [
        'enabled' => env('WEATHER_WIDGET_ENABLED', true),
        'auto_refresh_interval' => (int) env('WEATHER_AUTO_REFRESH_INTERVAL', 1800), // 30 minutes in seconds
        'show_detailed_info' => env('WEATHER_SHOW_DETAILED_INFO', true),
        'temperature_unit' => env('WEATHER_TEMPERATURE_UNIT', 'celsius'), // celsius or fahrenheit
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for weather data caching
    |
    */

    'cache_ttl' => (int) env('WEATHER_CACHE_TTL', 900), // 15 minutes in seconds
    'retry_attempts' => (int) env('WEATHER_RETRY_ATTEMPTS', 3),
    'request_timeout' => (int) env('WEATHER_REQUEST_TIMEOUT', 10), // seconds

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for API rate limiting protection
    |
    */

    'rate_limiting' => [
        'enabled' => env('WEATHER_RATE_LIMITING_ENABLED', true),
        'max_requests_per_minute' => (int) env('WEATHER_MAX_REQUESTS_PER_MINUTE', 60),
        'max_requests_per_hour' => (int) env('WEATHER_MAX_REQUESTS_PER_HOUR', 1000),
    ],
];
