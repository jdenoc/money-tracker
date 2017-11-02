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

$factory->define(App\Institution::class, function(Faker\Generator $faker){
    return [
        'name'=>$faker->company,
        'active'=>$faker->boolean
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Account::class, function(Faker\Generator $faker){
    $account_types = App\AccountType::get_enum_values('type');
    $account_name = $faker->company.' '.$account_types[array_rand($account_types)];
    $disabled = $faker->boolean;
    return [
        'name'=>$account_name,         // this is supposed to be a bank account name
        'institution_id'=>$faker->randomNumber(),
        'disabled'=>$disabled,
        'total'=>$faker->randomFloat(2, -1000, 1000),   // -1000.00 < total < 1000.00
        'disabled_stamp'=>$disabled ? $faker->date("Y-m-d H:i:s") : null
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\AccountType::class, function(Faker\Generator $faker){
    $bank_account_number = $faker->bankAccountNumber;
    $account_types = App\AccountType::get_enum_values('type');
    $account_type = $account_types[array_rand($account_types)];

    return [
        'type'=>$account_type,
        'last_digits'=>substr($bank_account_number, strlen($bank_account_number)-5, 4),
        'type_name'=>$faker->word.' '.$account_type,
        'account_id'=>$faker->randomNumber(),
        'disabled'=>false,
        'create_stamp'=>$faker->date("Y-m-d H:i:s"),
        'modified_stamp'=>$faker->date("Y-m-d H:i:s"),
        'disabled_stamp'=>null,
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Entry::class, function(Faker\Generator $faker){
    return [
        'entry_date'=>$faker->date(),
        'account_type'=>$faker->randomNumber(),
        'entry_value'=>$faker->randomFloat(2, 0, 100),  // 0.00 < entry_value < 100.00
        'memo'=>$faker->words(3, true),
        'expense'=>$faker->boolean,
        'confirm'=>$faker->boolean
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Attachment::class, function(Faker\Generator $faker){
    return [
        'uuid'=>$faker->uuid,
        'attachment'=>$faker->word.'.'.$faker->fileExtension,
        'entry_id'=>$faker->randomNumber(),
        'stamp'=>date('Y-m-d H:i:s')
    ];
});