<?php

use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Tag::class, function(Faker $faker){
    return [
        'name'=>$faker->unique()->colorName(),
    ];
});
