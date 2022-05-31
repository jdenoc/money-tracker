<?php

namespace App\Traits\Tests;

use Jdenoc\BulmaColors\BulmaColors;

trait WithBulmaColors {

    /**
     * @var BulmaColors
     */
    protected $bulmaColors;

    protected function setupBulmaColors(){
        $this->bulmaColors = new BulmaColors();
    }

}
