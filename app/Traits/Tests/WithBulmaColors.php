<?php

namespace App\Traits\Tests;

use Jdenoc\TailwindColors\BulmaColors;

trait WithBulmaColors {

    protected $bulmaColors;

    protected function setupBulmaColors(){
        $this->bulmaColors = new BulmaColors();
    }

}
