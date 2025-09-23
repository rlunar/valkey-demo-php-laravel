<?php

namespace App\Http\Controllers;

use App\Models\WeatherCity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Get weather data for a random city (mocked OpenWeatherMap response)
     */
    public function getRandomWeather(): JsonResponse
    {
        // Simulate external API latency (250-750ms)
        $latency = rand(250, 750);
        usleep($latency * 1000); // Convert to microseconds

        $city = WeatherCity::getRandomCity();

        if (! $city) {
            return response()->json(['error' => 'No cities available'], 404);
        }

        // Mock weather data similar to OpenWeatherMap API
        $weatherData = $this->generateMockWeatherData($city);

        return response()->json($weatherData);
    }

    /**
     * Get weather data for multiple random cities
     */
    public function getMultipleRandomWeather(Request $request): JsonResponse
    {
        // Simulate external API latency (250-750ms)
        $latency = rand(250, 750);
        usleep($latency * 1000); // Convert to microseconds

        $count = min($request->get('count', 5), 10); // Limit to 10 cities max
        $cities = WeatherCity::getRandomCities($count);

        if ($cities->isEmpty()) {
            return response()->json(['error' => 'No cities available'], 404);
        }

        $weatherData = $cities->map(function ($city) {
            return $this->generateMockWeatherData($city);
        });

        return response()->json($weatherData);
    }

    /**
     * Generate mock weather data similar to OpenWeatherMap API response
     */
    private function generateMockWeatherData(WeatherCity $city): array
    {
        $weatherConditions = [
            ['main' => 'Clear', 'description' => 'clear sky', 'icon' => '01d'],
            ['main' => 'Clouds', 'description' => 'few clouds', 'icon' => '02d'],
            ['main' => 'Clouds', 'description' => 'scattered clouds', 'icon' => '03d'],
            ['main' => 'Clouds', 'description' => 'broken clouds', 'icon' => '04d'],
            ['main' => 'Rain', 'description' => 'light rain', 'icon' => '10d'],
            ['main' => 'Rain', 'description' => 'moderate rain', 'icon' => '10d'],
            ['main' => 'Thunderstorm', 'description' => 'thunderstorm', 'icon' => '11d'],
            ['main' => 'Snow', 'description' => 'light snow', 'icon' => '13d'],
            ['main' => 'Mist', 'description' => 'mist', 'icon' => '50d'],
        ];

        $weather = $weatherConditions[array_rand($weatherConditions)];
        $temp = rand(-10, 40); // Temperature in Celsius
        $humidity = rand(30, 90);
        $pressure = rand(990, 1030);
        $windSpeed = rand(0, 20);

        return [
            'coord' => [
                'lon' => (float) $city->longitude,
                'lat' => (float) $city->latitude,
            ],
            'weather' => [$weather],
            'base' => 'stations',
            'main' => [
                'temp' => $temp,
                'feels_like' => $temp + rand(-3, 3),
                'temp_min' => $temp - rand(0, 5),
                'temp_max' => $temp + rand(0, 5),
                'pressure' => $pressure,
                'humidity' => $humidity,
            ],
            'visibility' => rand(5000, 10000),
            'wind' => [
                'speed' => $windSpeed,
                'deg' => rand(0, 360),
            ],
            'clouds' => [
                'all' => rand(0, 100),
            ],
            'dt' => now()->timestamp,
            'sys' => [
                'type' => 2,
                'id' => rand(1000, 9999),
                'country' => $city->country_code,
                'sunrise' => now()->startOfDay()->addHours(6)->timestamp,
                'sunset' => now()->startOfDay()->addHours(18)->timestamp,
            ],
            'timezone' => $city->timezone ?? 0,
            'id' => $city->id,
            'name' => $city->name,
            'cod' => 200,
        ];
    }
}
