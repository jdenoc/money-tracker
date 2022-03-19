<?php

use Faker\Generator as Faker;
use App\Helpers\DatabaseFactoryConstants AS FactoryConstants;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Entry::class, static function(Faker $faker){
    return [
        'entry_date'=>$faker->date(),
        'account_type_id'=>$faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
        'entry_value'=>$faker->randomFloat(FactoryConstants::CURRENCY_MAX_DECIMAL, 0, 100),  // 0.00 < entry_value < 100.00
        'memo'=>$faker->words(3, true),
        'expense'=>$faker->boolean(),
        'confirm'=>$faker->boolean(),
        'disabled'=>false,
        'transfer_entry_id'=>null
    ];
});

