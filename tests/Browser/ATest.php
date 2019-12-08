<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

/**
 * Class ATest
 *
 * @package Tests\Browser
 *
 * @group demo
 */
class ATest extends DuskTestCase {

    /**
     * A basic browser test to make sure selenium integration works
     *
     * @throws \Throwable
     */
    public function testBasicExample(){
        $this->browse(function (Browser $browser) {
            $browser->visit('/laravel')
                    ->assertSee('Laravel');
        });
    }

    public function testTitleIsCorrect(){
        $this->browse(function (Browser $browser){
            $browser->visit('/')
                ->assertTitleContains("Money Tracker | HOME");
        });
    }
}
