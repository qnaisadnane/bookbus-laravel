<?php

namespace Database\Factories;

use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

class VilleFactory extends Factory
{
    protected $model = Ville::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
        ];
    }
}
