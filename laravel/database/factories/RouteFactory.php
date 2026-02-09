<?php

namespace Database\Factories;

use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        $cityPairs = [
            ['Casa', 'Marrakech'],
            ['Casa', 'Fez'],
            ['Marrakech', 'Agadir'],
            ['Fez', 'Tangier'],
            ['Rabat', 'Casablanca'],
        ];

        $pair = $this->faker->randomElement($cityPairs);

        return [
            'nom' => 'L' . rand(100, 999),
            'description' => "{$pair[0]} - {$pair[1]} Express",
        ];
    }
}
