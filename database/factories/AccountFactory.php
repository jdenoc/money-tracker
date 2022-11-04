<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use App\Providers\Faker\ProjectCurrencyCodeProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory {

    public function definition(): array {
        $this->faker->addProvider(new ProjectCurrencyCodeProvider($this->faker));
        $account_name = $this->faker->company().' account';
        $disabled = $this->faker->boolean();
        return [
            'name'=>$account_name,         // this is supposed to be a bank account name
            'institution_id'=>$this->faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'disabled'=>$disabled,
            'total'=>$this->faker->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, -1000, 1000),   // -1000.00 < total < 1000.00
            'currency'=>$this->faker->currencyCode(),
            'disabled_stamp'=>$disabled ? $this->faker->date(FactoryConstants::DATE_FORMAT) : null
        ];
    }

}
