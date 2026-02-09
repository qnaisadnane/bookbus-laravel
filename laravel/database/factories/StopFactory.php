<?php

namespace Database\Factories;

use App\Models\Stop;
use Illuminate\Database\Eloquent\Factories\Factory;

class StopFactory extends Factory
{
    protected $model = Stop::class;

    public function definition(): array
    {
        return [
            'route_id' => 1,
            'station_id' => 1,
            'order' => $this->faker->randomDigit(),
            'duration_minutes' => $this->faker->numberBetween(5, 30),
        ];
    }
}
