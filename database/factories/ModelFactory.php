<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Tag::class, function(Faker\Generator $faker){
    return [
        'tag'=>$faker->word
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Account::class, function(Faker\Generator $faker){
    return [
        'account'=>$faker->company,         // this is supposed to be a bank name
        'total'=>$faker->randomFloat(2, -9999.99, 9999.99),
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\AccountType::class, function(Faker\Generator $faker){
    $bank_account_number = $faker->bankAccountNumber;
    $account_types = App\Helpers\DatabaseHelper::get_enum_values('account_types', 'type');
    $account_type = $account_types[array_rand($account_types)];

    return [
        'type'=>$account_type,
        'last_digits'=>substr($bank_account_number, strlen($bank_account_number)-5, 4),
        'type_name'=>$faker->word.' '.$account_type,
        'account_group'=>$faker->randomNumber()
    ];
});