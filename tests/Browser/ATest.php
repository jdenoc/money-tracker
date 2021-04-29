<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
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

    private static string $LARAVEL_FAVICON_PATH_PREFIX = '/laravel-favicon/';
    private static string $FAVICON_PATH_PREFIX = 'imgs/favicon/';

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

    public function providerTitleIsCorrect():array{
        return [
            // [$url, $title]
            'home'=>['/', "Money Tracker | HOME"],
            'stats'=>['/stats', "Money Tracker | STATS"],
            'settings'=>['/settings', 'Money Tracker | Settings'],
        ];
    }

    /**
     * @dataProvider providerTitleIsCorrect
     * @param string $url
     * @param string $title
     * @throws \Throwable
     */
    public function testTitleAndFaviconAreCorrectAndPresent(string $url, string $title){
        $favicon_file_paths = [
            self::$FAVICON_PATH_PREFIX.'favicon-16x16.png',
            self::$FAVICON_PATH_PREFIX.'favicon-32x32.png',
            self::$FAVICON_PATH_PREFIX.'apple-touch-icon.png',
            self::$FAVICON_PATH_PREFIX.'android-chrome-192x192.png',
            self::$FAVICON_PATH_PREFIX.'android-chrome-512x512.png',
            self::$FAVICON_PATH_PREFIX.'site.webmanifest'
        ];
        foreach($favicon_file_paths as $favicon_file_path){
            $this->assertFileExists(public_path($favicon_file_path));
        }

        $this->browse(function (Browser $browser) use ($url, $title){
            $browser->visit($url)->assertTitleContains($title);

            $link_elements = $browser->driver
                ->findElements(WebDriverBy::cssSelector('link'));
            foreach($link_elements as $link_element){
                if(in_array($link_element->getAttribute('rel'), ['apple-touch-icon', 'icon'])){
                    $this->assertStringContainsString(
                        self::$LARAVEL_FAVICON_PATH_PREFIX.self::$FAVICON_PATH_PREFIX,
                        $link_element->getAttribute('href')
                    );
                }
            }
        });
    }

}