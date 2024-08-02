<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use App\Providers\Faker\ProjectFilenameProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory {

    public function definition(): array {
        fake()->addProvider(new ProjectFilenameProvider(fake()));
        return [
            'uuid'=>fake()->uuid(),
            'name'=>fake()->filename(),
            'entry_id'=>fake()->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'stamp'=>date(FactoryConstants::DATE_FORMAT)
        ];
    }

}
