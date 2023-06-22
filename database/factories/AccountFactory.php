<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use App\Providers\Faker\ProjectCurrencyCodeProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory {

    public function definition(): array {
        fake()->addProvider(new ProjectCurrencyCodeProvider(fake()));
        return [
            'name'=>fake()->company().' account',    // this is supposed to be a bank account name
            'institution_id'=>fake()->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'currency'=>fake()->currencyCode(),
            'total'=>fake()->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, -100000, 100000),    // -1000.00 < total < 1000.00
            'disabled_stamp'=>null
        ];
    }

    /**
     * Indicate that an account is "disabled"
     */
    public function disabled(): Factory {
        return $this->state(function() {
            return [
                'disabled_stamp'=>fake()->date(FactoryConstants::DATE_FORMAT)
            ];
        });
    }

}
