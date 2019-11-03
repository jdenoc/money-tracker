<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;

/**
 * Class ATest
 *
 * @package Tests\Browser
 *
 * @group demo
 */
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
            $browser->visit('/laravel')
                    ->assertSee('Laravel');
        });
    }

    public function testLegacySiteIsAvailable(){
        $this->markTestSkipped("deprecating this endpoint");
        $this->browse(function (Browser $browser){
            $browser->visit('/legacy')
                ->assertTitleContains("Money Tracker | Legacy");
        });
    }

    public function testTitleIsCorrect(){
        $this->browse(function (Browser $browser){
            $browser->visit('/')
                ->assertTitleContains("Money Tracker | HOME");
        });
    }
}
