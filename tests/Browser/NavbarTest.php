<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\BrowserDimensions as DuskTraitBrowserDimensions;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use Tests\Browser\Pages\HomePage;
use Tests\Browser\Pages\SettingsPage;
use Tests\Browser\Pages\StatsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

/**
 * Class NavbarTest
 *
 * @package Tests\Browser
 *
 * @group navigation
 * @group home
 */
class NavbarTest extends DuskTestCase {

    use DuskTraitBrowserDimensions;
    use DuskTraitNavbar;
    use DuskTraitLoading;

    private $_label_add_entry = "Add Entry";
    private $_label_filter = "Filter";
    private $_label_transfer = "Add Transfer";
    private $_label_version = "Version:";
    private $_label_home = "Home";
    private $_label_stats = "Statistics";
    private $_label_settings = "Settings";
    private $_label_logout = "Logout";

    public function providerNavbarLogoVisible():array{
        return [
            'home'=>['home'],           // test 1/25
            'stats'=>['stats'],         // test 2/25
            'settings'=>['settings'],   // test 3/25
        ];
    }

    /**
     * @dataProvider providerNavbarLogoVisible
     * @param string $page_name
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavbarLogoVisible(string $page_name){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject){
            $browser->visit($pageObject);
            $this->waitForLoadingToStop($browser);
            $this->assertLogoVisible($browser);
        });
    }

    public function providerNavbarAddEntryButton():array{
        return [
            'home'=>['home', true],             // test 4/25
            'stats'=>['stats', false],          // test 5/25
            'settings'=>['settings', false],    // test 6/25
        ];
    }

    /**
     * @dataProvider providerNavbarAddEntryButton
     * @param string $page_name
     * @param bool $exists
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavbarAddEntryButton(string $page_name, bool $exists){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject, $exists){
            $browser->visit($pageObject);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar) use ($exists){
                if($exists){
                    $navbar
                        ->assertVisible(self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN)
                        ->assertSeeIn(self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN, $this->_label_add_entry);
                } else {
                    $navbar
                        ->assertMissing(self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN)
                        ->assertDontSee($this->_label_add_entry);
                }
            });
        });
    }

    public function providerNavbarAddTransferButton():array{
        return [
            'home'=>['home', true],             // test 7/25
            'stats'=>['stats', false],          // test 8/25
            'settings'=>['settings', false],    // test 9/25
        ];
    }

    /**
     * @dataProvider providerNavbarAddTransferButton
     * @param string $page_name
     * @param bool $exists
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavbarAddTransferButton(string $page_name, bool $exists){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject, $exists){
            $browser->visit($pageObject);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar) use ($exists){
                if($exists){
                    $navbar
                        ->assertVisible(self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN)
                        ->assertSeeIn(self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN, $this->_label_transfer);
                } else {
                    $navbar
                        ->assertMissing(self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN)
                        ->assertDontSee($this->_label_transfer);
                }
            });
        });
    }

    public function providerNavbarFilterButtonExists():array{
        return [
            'home'=>['home', true],             // test 10/25
            'stats'=>['stats', false],          // test 11/25
            'settings'=>['settings', false],    // test 12/25
        ];
    }

    /**
     * @dataProvider providerNavbarFilterButtonExists
     * @param string $page_name
     * @param bool $exists
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavbarFilterButtonExists(string $page_name, bool $exists){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject, $exists){
            $browser->visit($pageObject);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar) use ($exists){
                if ($exists){
                    $navbar
                        ->assertVisible(self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN)
                        ->assertSeeIn(self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN, $this->_label_filter);
                } else {
                    $navbar
                        ->assertMissing(self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN)
                        ->assertDontSee($this->_label_filter);
                }
            });
        });
    }

    public function providerNavbarClickProfileImage():array{
        return [
            'home'=>['home'],           // test 13/25
            'stats'=>['stats'],         // test 14/25
            'settings'=>['settings'],   // test 15/25
        ];
    }

    /**
     * @dataProvider providerNavbarClickProfileImage
     * @param string $page_name
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavbarClickProfileImage(string $page_name){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($page_name, $pageObject){
            $browser->visit($pageObject);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar) use ($page_name){
                $navbar
                    ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN_BTN)
                    ->assertMissing(self::$SELECTOR_NAVBAR_DROPDOWN)
                    ->assertVisible(self::$SELECTOR_NAVBAR_PROFILE)
                    ->click(self::$SELECTOR_NAVBAR_DROPDOWN_BTN)
                    ->pause(self::$WAIT_ONE_TENTH_OF_A_SECOND_IN_MILLISECONDS)
                    ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN)
                    ->within(self::$SELECTOR_NAVBAR_DROPDOWN, function(Browser $navbar_dropdown) use ($page_name){
                        $navbar_dropdown
                            ->assertVisible(self::$SELECTOR_NAVBAR_USERNAME)
                            ->assertSeeIn(self::$SELECTOR_NAVBAR_VERSION, $this->_label_version);
                        if($page_name == 'home'){
                            $navbar_dropdown->assertDontSeeLink($this->_label_home);
                        } else {
                            $navbar_dropdown->assertSeeLink($this->_label_home);
                        }
                        if($page_name == 'stats'){
                            $navbar_dropdown->assertDontSeeLink($this->_label_stats);
                        } else {
                            $navbar_dropdown->assertSeeLink($this->_label_stats);
                        }
                        if($page_name == 'settings'){
                            $navbar_dropdown->assertDontSeeLink($this->_label_settings);
                        } else {
                            $navbar_dropdown->assertSeeLink($this->_label_settings);
                        }
                        $navbar_dropdown->assertSeeLink($this->_label_logout);
                });
            });
        });
    }

    public function providerNavbarSmScreenElementsNotVisibleByDefault():array{
        return [
            'home'=>['home'],           // test 16/25
            'stats'=>['stats'],         // test 17/25
            'settings'=>['settings'],   // test 18/25
        ];
    }

    /**
     * @dataProvider providerNavbarClickProfileImage
     * @param string $page_name
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavbarSmScreenElementsNotVisibleByDefault(string $page_name){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject){
            $browser->visit($pageObject);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function (Browser $navbar) {
                $navbar
                    ->assertDontSee(self::$SELECTOR_NAVBAR_SM_SIDEBAR_BTN)
                    ->assertDontSee(self::$SELECTOR_NAVBAR_SM_BURGER_BTN);
            });
        });
    }

    public function providerNavbarMdScreenElementsNotVisibleForSmScreenSize():array{
        return [
            'home'=>['home'],           // test 19/25
            'stats'=>['stats'],         // test 20/25
            'settings'=>['settings'],   // test 21/25
        ];
    }

    /**
     * @dataProvider providerNavbarMdScreenElementsNotVisibleForSmScreenSize
     * @param string $page_name
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavbarMdScreenElementsNotVisibleForSmScreenSize(string $page_name){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject){
            $browser
                ->visit($pageObject)
                ->resize(self::$MAX_SM_BROWSER_WIDTH_PX, self::$DEFAULT_BROWSER_HEIGHT_PX);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function (Browser $navbar) {
                $this->assertLogoVisible($navbar);  // make sure the logo is still visible
                $navbar
                    ->assertDontSee(self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN)
                    ->assertDontSee($this->_label_add_entry)
                    ->assertDontSee(self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN)
                    ->assertDontSee($this->_label_transfer)
                    ->assertDontSee(self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN)
                    ->assertDontSee($this->_label_filter)
                    ->assertDontSee(self::$SELECTOR_NAVBAR_DROPDOWN_BTN);
            });
        });
    }

    public function providerNavberSmScreenBurgerMenu():array{
        return [
            'home'=>['home'],           // test 22/25
            'stats'=>['stats'],         // test 23/25
            'settings'=>['settings'],   // test 24/25
        ];
    }

    /**
     * @dataProvider providerNavberSmScreenBurgerMenu
     * @param string $page_name
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavberSmScreenBurgerMenu(string $page_name){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject, $page_name){
            $browser
                ->visit($pageObject)
                ->resize(self::$MAX_SM_BROWSER_WIDTH_PX, self::$DEFAULT_BROWSER_HEIGHT_PX);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar) use ($page_name){
                $navbar
                    ->assertVisible(self::$SELECTOR_NAVBAR_SM_BURGER_BTN)
                    ->assertMissing(self::$SELECTOR_NAVBAR_SM_BURGER_MENU)
                    ->click(self::$SELECTOR_NAVBAR_SM_BURGER_BTN)
                    ->assertVisible(self::$SELECTOR_NAVBAR_SM_BURGER_MENU)
                    ->within(self::$SELECTOR_NAVBAR_SM_BURGER_MENU, function(Browser $navbar_dropdown) use ($page_name){
                        $navbar_dropdown
                            ->assertVisible(self::$SELECTOR_NAVBAR_PROFILE)
                            ->assertVisible(self::$SELECTOR_NAVBAR_USERNAME)
                            ->assertSeeIn(self::$SELECTOR_NAVBAR_VERSION, $this->_label_version);
                        if($page_name == 'home'){
                            $navbar_dropdown
                                ->assertDontSeeLink($this->_label_home)
                                ->assertSeeIn(self::$SELECTOR_NAVBAR_SM_NEW_ENTRY_BTN, $this->_label_add_entry)
                                ->assertSeeIn(self::$SELECTOR_NAVBAR_SM_ADD_TRANSFER_BTN, $this->_label_transfer)
                                ->assertSeeIn(self::$SELECTOR_NAVBAR_SM_OPEN_FILTER_BTN, $this->_label_filter);
                        } else {
                            $navbar_dropdown->assertSeeLink($this->_label_home);
                        }
                        if($page_name == 'stats'){
                            $navbar_dropdown->assertDontSeeLink($this->_label_stats);
                        } else {
                            $navbar_dropdown->assertSeeLink($this->_label_stats);
                        }
                        if($page_name == 'settings'){
                            $navbar_dropdown->assertDontSeeLink($this->_label_settings);
                        } else {
                            $navbar_dropdown->assertSeeLink($this->_label_settings);
                        }
                        $navbar_dropdown->assertSeeLink($this->_label_logout);
                    });
            });
        });
    }

    public function providerNavberSmScreenSidebar():array{
        return [
            'home'=>['home'],           // test 25/25
            'stats'=>['stats'],         // test 26/25
            'settings'=>['settings'],   // test 27/25
        ];
    }

    /**
     * @dataProvider providerNavberSmScreenSidebar
     * @param string $page_name
     * @throws Throwable
     *
     * @group navigation-2
     * test ?/25
     */
    public function testNavberSmScreenSidebar(string $page_name){
        $pageObject = $this->getDuskPageObject($page_name);
        $this->browse(function(Browser $browser) use ($pageObject){
            $browser
                ->visit($pageObject)
                ->resize(self::$MAX_SM_BROWSER_WIDTH_PX, self::$DEFAULT_BROWSER_HEIGHT_PX);
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                $navbar->assertVisible(self::$SELECTOR_NAVBAR_SM_SIDEBAR_BTN);
                $this->markTestIncomplete('need to implement this functionality first');
            });
        });
    }

    private function getDuskPageObject(string $page_name){
        switch($page_name){
            case 'home':
                return new HomePage();
            case 'stats':
                return new StatsPage();
            case 'settings':
                return new SettingsPage();
            default:
                throw new \UnexpectedValueException("Page name provided [$page_name] does not exist or is unsupported");
        }
    }

}
