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

    public function providerTitleIsCorrect(){
        return [
            // [$url, $title]
            'home'=>['/', "Money Tracker | HOME"],
            'stats'=>['/stats', "Money Tracker | STATS"],
        ];
    }

    /**
     * @dataProvider providerTitleIsCorrect
     * @param string $url
     * @param string $title
     * @throws \Throwable
     */
    public function testTitleAndFaviconAreCorrectAndPresent($url, $title){
        $this->browse(function (Browser $browser) use ($url, $title){
            $browser->visit($url)
                ->assertTitleContains($title)
                ->assertSourceHas('<link rel="icon" type="image/png" sizes="32x32" href="/laravel-favicon/');

            $favicon_file_paths = [
                'public/imgs/favicon/favicon-16x16.png',
                'public/imgs/favicon/favicon-32x32.png',
                'public/imgs/favicon/apple-touch-icon.png',
                'public/imgs/favicon/android-chrome-192x192.png',
                'public/imgs/favicon/android-chrome-512x512.png',
                'public/imgs/favicon/site.webmanifest'
            ];
            foreach($favicon_file_paths as $favicon_file_path){
                $this->assertFileExists($favicon_file_path);
            }
        });
    }

}