<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait Loading {

    use WaitTimes;

    public function waitForLoadingToStop(Browser $browser){
        $selector_loading = '#loading-modal';
        $browser->waitUntilMissing($selector_loading, self::$WAIT_SECONDS_LONG);
    }

}
