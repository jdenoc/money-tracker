<?php

use Faker\Generator as Faker;
use App\Helpers\DatabaseFactoryConstants AS FactoryConstants;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\AccountType::class, function(Faker $faker){
    $bank_account_number = $faker->bankAccountNumber;
    $account_types = App\AccountType::get_enum_values('type');
    $account_type = $account_types[array_rand($account_types)];

    return [
        'type'=>$account_type,
        'last_digits'=>substr($bank_account_number, strlen($bank_account_number)-5, 4),
        'name'=>$faker->word.' '.$account_type,
        'account_id'=>$faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
        'disabled'=>false,
        'create_stamp'=>$faker->date(FactoryConstants::DATE_FORMAT),
        'modified_stamp'=>$faker->date(FactoryConstants::DATE_FORMAT),
        'disabled_stamp'=>null,
    ];
});
