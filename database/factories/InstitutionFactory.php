<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory {

    public function definition(): array {
        return [
            'name'=>$this->faker->company(),
            'disabled_stamp'=>$this->faker->boolean() ? $this->faker->date(FactoryConstants::DATE_FORMAT) : null
        ];
    }

}
