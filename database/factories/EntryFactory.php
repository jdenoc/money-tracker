<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants AS FactoryConstants;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryFactory extends Factory {

    public function definition():array {
        return [
            'entry_date'=>$this->faker->dateTimeBetween('-15 months', 'now')->format('Y-m-d'),
            'account_type_id'=>$this->faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'entry_value'=>$this->faker->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, 0, 100),  // 0.00 < entry_value < 100.00
            'memo'=>$this->faker->words(3, true),
            'expense'=>$this->faker->boolean(),
            'confirm'=>$this->faker->boolean(),
            'disabled'=>false,
            'transfer_entry_id'=>null
        ];
    }

}

