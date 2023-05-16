<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory {

    public function definition(): array {
        return [
            'name'=>fake()->company(),
            'active'=>fake()->boolean()
        ];
    }

}
