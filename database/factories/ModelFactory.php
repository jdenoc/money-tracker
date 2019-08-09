<?php

use Faker\Generator as Faker;
use App\Helpers\DatabaseFactoryConstants AS FactoryConstants;

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
$factory->define(App\Tag::class, function(Faker $faker){
    return [
        'name'=>$faker->unique()->safeColorName
    ];
});

$factory->define(App\Institution::class, function(Faker $faker){
    return [
        'name'=>$faker->company,
        'active'=>$faker->boolean
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Account::class, function(Faker $faker){
    $faker->addProvider(new App\Providers\Faker\ProjectCurrencyCodeProvider($faker));
    $account_name = $faker->company.' account';
    $disabled = $faker->boolean;
    return [
        'name'=>$account_name,         // this is supposed to be a bank account name
        'institution_id'=>$faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
        'disabled'=>$disabled,
        'total'=>$faker->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, -1000, 1000),   // -1000.00 < total < 1000.00
        'currency'=>$faker->currencyCode,
        'disabled_stamp'=>$disabled ? $faker->date(FactoryConstants::DATE_FORMAT) : null
    ];
});

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

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Entry::class, function(Faker $faker){
    return [
        'entry_date'=>$faker->date(),
        'account_type_id'=>$faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
        'entry_value'=>$faker->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, 0, 100),  // 0.00 < entry_value < 100.00
        'memo'=>$faker->words(3, true),
        'expense'=>$faker->boolean,
        'confirm'=>$faker->boolean,
        'transfer_entry_id'=>null
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Attachment::class, function(Faker $faker){
    $faker->addProvider(new App\Providers\Faker\ProjectFilenameProvider($faker));
    return [
        'uuid'=>$faker->uuid,
        'name'=>$faker->filename,
        'entry_id'=>$faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
        'stamp'=>date(FactoryConstants::DATE_FORMAT)
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
    ];
});