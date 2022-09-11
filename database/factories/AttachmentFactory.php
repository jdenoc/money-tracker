<?php

namespace Database\Factories;

use App\Helpers\DatabaseFactoryConstants as FactoryConstants;
use App\Providers\Faker\ProjectFilenameProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory {

    public function definition(): array {
        $this->faker->addProvider(new ProjectFilenameProvider($this->faker));
        return [
            'uuid'=>$this->faker->uuid(),
            'name'=>$this->faker->filename(),
            'entry_id'=>$this->faker->randomNumber(FactoryConstants::MAX_RAND_ID_LENGTH, true),
            'stamp'=>date(FactoryConstants::DATE_FORMAT)
        ];
    }

}
