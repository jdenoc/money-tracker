<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jdenoc\Faker\TailwindColors\Provider as TailwindColorsProvider;

class TagFactory extends Factory {

    public function definition():array {
        $this->faker->addProvider(new TailwindColorsProvider($this->faker));
        return [
            'name'=>$this->faker->unique()->tailwindColorName(),
        ];
    }

}
