<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WeatherCity;

class WeatherCitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Argentina
            ['name' => 'Buenos Aires', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -34.6118, 'longitude' => -58.3960, 'timezone' => -10800],
            ['name' => 'Córdoba', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -31.4201, 'longitude' => -64.1888, 'timezone' => -10800],
            ['name' => 'Rosario', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -32.9442, 'longitude' => -60.6505, 'timezone' => -10800],
            ['name' => 'Mendoza', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -32.8895, 'longitude' => -68.8458, 'timezone' => -10800],
            ['name' => 'La Plata', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -34.9215, 'longitude' => -57.9545, 'timezone' => -10800],
            ['name' => 'Mar del Plata', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -38.0055, 'longitude' => -57.5426, 'timezone' => -10800],
            ['name' => 'Salta', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -24.7821, 'longitude' => -65.4232, 'timezone' => -10800],
            ['name' => 'Santa Fe', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -31.6333, 'longitude' => -60.7000, 'timezone' => -10800],
            ['name' => 'San Juan', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -31.5375, 'longitude' => -68.5364, 'timezone' => -10800],
            ['name' => 'Tucumán', 'country' => 'Argentina', 'country_code' => 'AR', 'latitude' => -26.8083, 'longitude' => -65.2176, 'timezone' => -10800],

            // Brazil
            ['name' => 'São Paulo', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -23.5505, 'longitude' => -46.6333, 'timezone' => -10800],
            ['name' => 'Rio de Janeiro', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -22.9068, 'longitude' => -43.1729, 'timezone' => -10800],
            ['name' => 'Brasília', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -15.8267, 'longitude' => -47.9218, 'timezone' => -10800],
            ['name' => 'Salvador', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -12.9714, 'longitude' => -38.5014, 'timezone' => -10800],
            ['name' => 'Fortaleza', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -3.7319, 'longitude' => -38.5267, 'timezone' => -10800],
            ['name' => 'Belo Horizonte', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -19.9167, 'longitude' => -43.9345, 'timezone' => -10800],
            ['name' => 'Manaus', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -3.1190, 'longitude' => -60.0217, 'timezone' => -14400],
            ['name' => 'Curitiba', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -25.4244, 'longitude' => -49.2654, 'timezone' => -10800],
            ['name' => 'Recife', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -8.0476, 'longitude' => -34.8770, 'timezone' => -10800],
            ['name' => 'Porto Alegre', 'country' => 'Brazil', 'country_code' => 'BR', 'latitude' => -30.0346, 'longitude' => -51.2177, 'timezone' => -10800],

            // USA
            ['name' => 'New York', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 40.7128, 'longitude' => -74.0060, 'timezone' => -18000],
            ['name' => 'Los Angeles', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 34.0522, 'longitude' => -118.2437, 'timezone' => -28800],
            ['name' => 'Chicago', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 41.8781, 'longitude' => -87.6298, 'timezone' => -21600],
            ['name' => 'Houston', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 29.7604, 'longitude' => -95.3698, 'timezone' => -21600],
            ['name' => 'Phoenix', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 33.4484, 'longitude' => -112.0740, 'timezone' => -25200],
            ['name' => 'Philadelphia', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 39.9526, 'longitude' => -75.1652, 'timezone' => -18000],
            ['name' => 'San Antonio', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 29.4241, 'longitude' => -98.4936, 'timezone' => -21600],
            ['name' => 'San Diego', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 32.7157, 'longitude' => -117.1611, 'timezone' => -28800],
            ['name' => 'Dallas', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 32.7767, 'longitude' => -96.7970, 'timezone' => -21600],
            ['name' => 'San Jose', 'country' => 'United States', 'country_code' => 'US', 'latitude' => 37.3382, 'longitude' => -121.8863, 'timezone' => -28800],

            // Mexico
            ['name' => 'Mexico City', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 19.4326, 'longitude' => -99.1332, 'timezone' => -21600],
            ['name' => 'Guadalajara', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 20.6597, 'longitude' => -103.3496, 'timezone' => -21600],
            ['name' => 'Monterrey', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 25.6866, 'longitude' => -100.3161, 'timezone' => -21600],
            ['name' => 'Puebla', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 19.0414, 'longitude' => -98.2063, 'timezone' => -21600],
            ['name' => 'Tijuana', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 32.5149, 'longitude' => -117.0382, 'timezone' => -28800],
            ['name' => 'León', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 21.1619, 'longitude' => -101.6921, 'timezone' => -21600],
            ['name' => 'Juárez', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 31.6904, 'longitude' => -106.4245, 'timezone' => -25200],
            ['name' => 'Zapopan', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 20.7214, 'longitude' => -103.3918, 'timezone' => -21600],
            ['name' => 'Mérida', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 20.9674, 'longitude' => -89.5926, 'timezone' => -21600],
            ['name' => 'Cancún', 'country' => 'Mexico', 'country_code' => 'MX', 'latitude' => 21.1619, 'longitude' => -86.8515, 'timezone' => -18000],

            // Germany
            ['name' => 'Berlin', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 52.5200, 'longitude' => 13.4050, 'timezone' => 3600],
            ['name' => 'Hamburg', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 53.5511, 'longitude' => 9.9937, 'timezone' => 3600],
            ['name' => 'Munich', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 48.1351, 'longitude' => 11.5820, 'timezone' => 3600],
            ['name' => 'Cologne', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 50.9375, 'longitude' => 6.9603, 'timezone' => 3600],
            ['name' => 'Frankfurt', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 50.1109, 'longitude' => 8.6821, 'timezone' => 3600],
            ['name' => 'Stuttgart', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 48.7758, 'longitude' => 9.1829, 'timezone' => 3600],
            ['name' => 'Düsseldorf', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 51.2277, 'longitude' => 6.7735, 'timezone' => 3600],
            ['name' => 'Dortmund', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 51.5136, 'longitude' => 7.4653, 'timezone' => 3600],
            ['name' => 'Essen', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 51.4556, 'longitude' => 7.0116, 'timezone' => 3600],
            ['name' => 'Leipzig', 'country' => 'Germany', 'country_code' => 'DE', 'latitude' => 51.3397, 'longitude' => 12.3731, 'timezone' => 3600],

            // Colombia
            ['name' => 'Bogotá', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 4.7110, 'longitude' => -74.0721, 'timezone' => -18000],
            ['name' => 'Medellín', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 6.2442, 'longitude' => -75.5812, 'timezone' => -18000],
            ['name' => 'Cali', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 3.4516, 'longitude' => -76.5320, 'timezone' => -18000],
            ['name' => 'Barranquilla', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 10.9685, 'longitude' => -74.7813, 'timezone' => -18000],
            ['name' => 'Cartagena', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 10.3910, 'longitude' => -75.4794, 'timezone' => -18000],
            ['name' => 'Cúcuta', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 7.8939, 'longitude' => -72.5078, 'timezone' => -18000],
            ['name' => 'Bucaramanga', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 7.1193, 'longitude' => -73.1227, 'timezone' => -18000],
            ['name' => 'Pereira', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 4.8133, 'longitude' => -75.6961, 'timezone' => -18000],
            ['name' => 'Santa Marta', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 11.2408, 'longitude' => -74.1990, 'timezone' => -18000],
            ['name' => 'Ibagué', 'country' => 'Colombia', 'country_code' => 'CO', 'latitude' => 4.4389, 'longitude' => -75.2322, 'timezone' => -18000],

            // United Kingdom
            ['name' => 'London', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 51.5074, 'longitude' => -0.1278, 'timezone' => 0],
            ['name' => 'Birmingham', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 52.4862, 'longitude' => -1.8904, 'timezone' => 0],
            ['name' => 'Manchester', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 53.4808, 'longitude' => -2.2426, 'timezone' => 0],
            ['name' => 'Glasgow', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 55.8642, 'longitude' => -4.2518, 'timezone' => 0],
            ['name' => 'Liverpool', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 53.4084, 'longitude' => -2.9916, 'timezone' => 0],
            ['name' => 'Leeds', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 53.8008, 'longitude' => -1.5491, 'timezone' => 0],
            ['name' => 'Sheffield', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 53.3811, 'longitude' => -1.4701, 'timezone' => 0],
            ['name' => 'Edinburgh', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 55.9533, 'longitude' => -3.1883, 'timezone' => 0],
            ['name' => 'Bristol', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 51.4545, 'longitude' => -2.5879, 'timezone' => 0],
            ['name' => 'Cardiff', 'country' => 'United Kingdom', 'country_code' => 'GB', 'latitude' => 51.4816, 'longitude' => -3.1791, 'timezone' => 0],

            // Australia
            ['name' => 'Sydney', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -33.8688, 'longitude' => 151.2093, 'timezone' => 36000],
            ['name' => 'Melbourne', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -37.8136, 'longitude' => 144.9631, 'timezone' => 36000],
            ['name' => 'Brisbane', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -27.4698, 'longitude' => 153.0251, 'timezone' => 36000],
            ['name' => 'Perth', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -31.9505, 'longitude' => 115.8605, 'timezone' => 28800],
            ['name' => 'Adelaide', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -34.9285, 'longitude' => 138.6007, 'timezone' => 34200],
            ['name' => 'Gold Coast', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -28.0167, 'longitude' => 153.4000, 'timezone' => 36000],
            ['name' => 'Newcastle', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -32.9283, 'longitude' => 151.7817, 'timezone' => 36000],
            ['name' => 'Canberra', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -35.2809, 'longitude' => 149.1300, 'timezone' => 36000],
            ['name' => 'Wollongong', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -34.4278, 'longitude' => 150.8931, 'timezone' => 36000],
            ['name' => 'Hobart', 'country' => 'Australia', 'country_code' => 'AU', 'latitude' => -42.8821, 'longitude' => 147.3272, 'timezone' => 36000],

            // Japan
            ['name' => 'Tokyo', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 35.6762, 'longitude' => 139.6503, 'timezone' => 32400],
            ['name' => 'Osaka', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 34.6937, 'longitude' => 135.5023, 'timezone' => 32400],
            ['name' => 'Yokohama', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 35.4437, 'longitude' => 139.6380, 'timezone' => 32400],
            ['name' => 'Nagoya', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 35.1815, 'longitude' => 136.9066, 'timezone' => 32400],
            ['name' => 'Sapporo', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 43.0642, 'longitude' => 141.3469, 'timezone' => 32400],
            ['name' => 'Kobe', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 34.6901, 'longitude' => 135.1956, 'timezone' => 32400],
            ['name' => 'Kyoto', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 35.0116, 'longitude' => 135.7681, 'timezone' => 32400],
            ['name' => 'Fukuoka', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 33.5904, 'longitude' => 130.4017, 'timezone' => 32400],
            ['name' => 'Kawasaki', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 35.5308, 'longitude' => 139.7029, 'timezone' => 32400],
            ['name' => 'Hiroshima', 'country' => 'Japan', 'country_code' => 'JP', 'latitude' => 34.3853, 'longitude' => 132.4553, 'timezone' => 32400],

            // China
            ['name' => 'Beijing', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 39.9042, 'longitude' => 116.4074, 'timezone' => 28800],
            ['name' => 'Shanghai', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 31.2304, 'longitude' => 121.4737, 'timezone' => 28800],
            ['name' => 'Guangzhou', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 23.1291, 'longitude' => 113.2644, 'timezone' => 28800],
            ['name' => 'Shenzhen', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 22.5431, 'longitude' => 114.0579, 'timezone' => 28800],
            ['name' => 'Chengdu', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 30.5728, 'longitude' => 104.0668, 'timezone' => 28800],
            ['name' => 'Nanjing', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 32.0603, 'longitude' => 118.7969, 'timezone' => 28800],
            ['name' => 'Wuhan', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 30.5928, 'longitude' => 114.3055, 'timezone' => 28800],
            ['name' => 'Tianjin', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 39.3434, 'longitude' => 117.3616, 'timezone' => 28800],
            ['name' => 'Shenyang', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 41.8057, 'longitude' => 123.4315, 'timezone' => 28800],
            ['name' => 'Hangzhou', 'country' => 'China', 'country_code' => 'CN', 'latitude' => 30.2741, 'longitude' => 120.1551, 'timezone' => 28800],
        ];

        foreach ($cities as $city) {
            WeatherCity::create($city);
        }
    }
}