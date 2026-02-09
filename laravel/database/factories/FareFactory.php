<?php

namespace Database\Factories;

use App\Models\Fare;
use Illuminate\Database\Eloquent\Factories\Factory;

class FareFactory extends Factory
{
    protected $model = Fare::class;

    public function definition(): array
    {
        return [
            'segment_id' => 1,
            'bus_type' => $this->faker->randomElement(['standard', 'comfort', 'premium']),
            'price' => $this->faker->numberBetween(50, 200),
            'active' => true,
        ];
    }
}
