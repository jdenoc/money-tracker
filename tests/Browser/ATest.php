<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Class ATest
 *
 * @package Tests\Browser
 *
 * @group demo
 */
class ATest extends DuskTestCase {

    private static string $LARAVEL_FAVICON_PATH_PREFIX = '/laravel-favicon/';
    private static string $FAVICON_PATH_PREFIX = 'favicon/';

    /**
     * A basic browser test to make sure selenium integration works
     *
     * @throws \Throwable
     *
     * @group demo-1
     * test 1/20
     */
    public function testBasicExample() {
        $this->browse(function(Browser $browser) {
            $browser
                ->visit('/laravel')
                ->assertSee('Laravel');
        });
    }

    public static function providerTitleIsCorrect(): array {
        return [
            // [$url, $title]
            'home' => ['/', "Money Tracker | HOME"],                  // test 2/20
            'stats' => ['/stats', "Money Tracker | STATS"],           // test 3/20
            'settings' => ['/settings', 'Money Tracker | Settings'],  // test 4/20
        ];
    }

    /**
     * @dataProvider providerTitleIsCorrect
     * @throws \Throwable
     *
     * @group demo-1
     * test ?/20
     */
    public function testTitleAndFaviconAreCorrectAndPresent(string $url, string $title) {
        $favicon_file_paths = [
            self::$FAVICON_PATH_PREFIX.'favicon-16x16.png',
            self::$FAVICON_PATH_PREFIX.'favicon-32x32.png',
            self::$FAVICON_PATH_PREFIX.'apple-touch-icon.png',
            self::$FAVICON_PATH_PREFIX.'android-chrome-192x192.png',
            self::$FAVICON_PATH_PREFIX.'android-chrome-512x512.png',
            self::$FAVICON_PATH_PREFIX.'site.webmanifest',
        ];
        foreach ($favicon_file_paths as $favicon_file_path) {
            $this->assertFileExists(public_path($favicon_file_path));
        }

        $this->browse(function(Browser $browser) use ($url, $title) {
            $browser
                ->visit($url)
                ->assertTitleContains($title);

            $link_elements = $browser->driver->findElements(WebDriverBy::cssSelector('link'));
            foreach ($link_elements as $link_element) {
                if (in_array($link_element->getAttribute('rel'), ['apple-touch-icon', 'icon'])) {
                    $this->assertStringContainsString(
                        self::$LARAVEL_FAVICON_PATH_PREFIX.self::$FAVICON_PATH_PREFIX,
                        $link_element->getAttribute('href')
                    );
                }
            }
        });
    }

}
