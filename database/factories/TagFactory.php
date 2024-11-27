<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jdenoc\Faker\TailwindColors\Provider as TailwindColorsProvider;

class TagFactory extends Factory {

    public function definition(): array {
        fake()->addProvider(new TailwindColorsProvider(fake()));
        return [
            'name' => fake()->unique()->tailwindColorName(),
        ];
    }

}
