<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait Loading {

    protected static $WAIT_SECONDS_LONG = 30;

    public function waitForLoadingToStop(Browser $browser){
        $selector_loading = '#loading-modal';
        $browser
            ->assertVisible($selector_loading)
            ->waitUntilMissing($selector_loading, self::$WAIT_SECONDS_LONG);
    }

}
