<?php

namespace App\Traits\Tests;

use Jdenoc\TailwindColors\TailwindColors;

/**
 * Trait BulmaColors
 *
 * @package App\Traits\Tests\Dusk
 */
trait WithTailwindColors {

    protected $tailwindColors;

    protected function setupTailwindColors(){
        $this->tailwindColors = new TailwindColors();
    }

}