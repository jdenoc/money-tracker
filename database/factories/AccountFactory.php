<?php

use Faker\Generator as Faker;
use App\Helpers\DatabaseFactoryConstants AS FactoryConstants;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Account::class, function(Faker $faker){
    $faker->addProvider(new App\Providers\Faker\ProjectCurrencyCodeProvider($faker));
    $account_name = $faker->company().' account';
    $disabled = $faker->boolean();
    return [
        'name'=>$account_name,         // this is supposed to be a bank account name
        'institution_id'=>$faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
        'disabled'=>$disabled,
        'total'=>$faker->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, -1000, 1000),   // -1000.00 < total < 1000.00
        'currency'=>$faker->currencyCode(),
        'disabled_stamp'=>$disabled ? $faker->date(FactoryConstants::DATE_FORMAT) : null
    ];
});

