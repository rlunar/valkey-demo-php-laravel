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
    |
    */

    'default_location' => [
        'lat' => env('WEATHER_DEFAULT_LAT', 40.7128),
        'lon' => env('WEATHER_DEFAULT_LON', -74.0060),
        'name' => env('WEATHER_DEFAULT_LOCATION', 'New York, NY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for weather data caching
    |
    */

    'cache_ttl' => 900, // 15 minutes in seconds
    'retry_attempts' => 3,
    'refresh_interval' => 1800, // 30 minutes in seconds
];
