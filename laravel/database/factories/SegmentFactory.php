<?php

namespace Database\Factories;

use App\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SegmentFactory extends Factory
{
    protected $model = Segment::class;

    public function definition(): array
    {
        return [
            'route_id' => 1,
            'departure_stop_id' => 1,
            'arrival_stop_id' => 2,
            'distance_km' => $this->faker->numberBetween(50, 300),
            'duration_minutes' => $this->faker->numberBetween(60, 480),
        ];
    }
}
