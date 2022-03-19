<?php

use Faker\Generator as Faker;
use App\Helpers\DatabaseFactoryConstants AS FactoryConstants;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Attachment::class, function(Faker $faker){
    $faker->addProvider(new App\Providers\Faker\ProjectFilenameProvider($faker));
    return [
        'uuid'=>$faker->uuid(),
        'name'=>$faker->filename(),
        'entry_id'=>$faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
        'stamp'=>date(FactoryConstants::DATE_FORMAT)
    ];
});