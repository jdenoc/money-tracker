<?php

use Faker\Generator as Faker;

/**
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(App\Institution::class, function (Faker $faker) {
    return [
        'name'=>$faker->company(),
        'active'=>$faker->boolean()
    ];
});