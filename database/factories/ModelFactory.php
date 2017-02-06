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
        'total'=>$faker->randomFloat(2),    // Float like 10.98
    ];
});
