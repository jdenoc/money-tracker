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
     * TODO: remove; this is just for testing
     */
    public function test_error_log_location(){
        error_log($this->getName(false));
        logger()->error($this->getName(false));
        logger(json_encode(ini_get_all()));
        self::fail("This test better fail!!!");
    }

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

    /**
     * @throws \Throwable
     */
    public function testTitleIsCorrect(){
        $this->browse(function (Browser $browser){
            $browser->visit('/')
                ->assertTitleContains("Money Tracker | HOME");
        });
    }

    /**
     * @throws \Throwable
     */
    public function testTitleIsCorrectOnStatsPage(){
        $this->browse(function (Browser $browser){
            $browser->visit('/stats')
                ->assertTitleContains("Money Tracker | STATS");
        });
    }
}
