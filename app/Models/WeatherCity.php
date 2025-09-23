<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'country_code',
        'latitude',
        'longitude',
        'timezone',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    /**
     * Get a random city from the database
     */
    public static function getRandomCity()
    {
        return static::inRandomOrder()->first();
    }

    /**
     * Get multiple random cities
     */
    public static function getRandomCities($count = 1)
    {
        return static::inRandomOrder()->limit($count)->get();
    }
}