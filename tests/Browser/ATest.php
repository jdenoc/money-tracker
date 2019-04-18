<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;

class ATest extends DuskTestCase {

    /**
     * Override the setUp() from DuskTestCase
     */
    protected function setUp(){
        BaseTestCase::setUp();
    }

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
