<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory {

    public function definition(): array {
        return [
            'name'=>fake()->company(),
            'disabled_stamp'=>null,
        ];
    }

    /**
     * Indicate that an institution is "disabled"
     */
    public function disabled(): Factory {
        return $this->state(function() {
            return [
                'disabled_stamp'=>$this->faker->date(FactoryConstants::DATE_FORMAT)
            ];
        });
    }

}
