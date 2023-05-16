<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryFactory extends Factory {

    public function definition(): array {
        return [
            'entry_date'=>fake()->dateTimeBetween('-15 months', 'now')->format('Y-m-d'),
            'account_type_id'=>fake()->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'entry_value'=>fake()->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, 0, 100),  // 0.00 < entry_value < 100.00
            'memo'=>fake()->words(3, true),
            'expense'=>fake()->boolean(),
            'confirm'=>fake()->boolean(),
            'disabled'=>false,
            'transfer_entry_id'=>null
        ];
    }

}
