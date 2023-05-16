<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use App\Providers\Faker\ProjectCurrencyCodeProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory {

    public function definition(): array {
        fake()->addProvider(new ProjectCurrencyCodeProvider(fake()));
        $account_name = fake()->company().' account';
        $disabled = fake()->boolean();
        return [
            'name'=>$account_name,         // this is supposed to be a bank account name
            'institution_id'=>fake()->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'disabled'=>$disabled,
            'currency'=>fake()->currencyCode(),
            'total'=>fake()->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, -1000, 1000),   // -1000.00 < total < 1000.00
            'disabled_stamp'=>$disabled ? fake()->date(FactoryConstants::DATE_FORMAT) : null
        ];
    }

}
