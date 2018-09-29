<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class ATest extends DuskTestCase {

    /**
     * A basic browser test to make sure selenium integration works
     */
    public function testBasicExample(){
        $this->browse(function (Browser $browser) {
            $browser->visit('/')    // TODO: move this somewhere else
                    ->assertSee('Laravel');
        });
    }
}
