<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\Dusk\ResizeBrowser as DuskTraitResizeBrowser;
use Tests\Browser\Pages\HomePage;
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

    use DuskTraitNavbar;
    use DuskTraitLoading;
    use DuskTraitResizeBrowser;

    private $_label_add_entry = "Add Entry";
    private $_label_filter = "Filter";
    private $_label_transfer = "Add Transfer";
    private $_label_version = "Version:";
    private $_label_home = "Home";
    private $_label_stats = "Statistics";
    private $_label_settings = "Settings";
    private $_label_logout = "Logout";

    public function setUp(): void {
        switch($this->getName()){
            case 'testBurgerMenuVisibleOnSmallerScreenWidthOnHomePage':
            case 'testBurgerMenuVisibleOnSmallerScreenWidthOnStatsPage':
                self::$RESIZE_BROWSER_WIDTH_PX = 1000;
                self::$RESIZE_BROWSER_HEIGHT_PX = 750;
                error_log("setting browser size for ".$this->getName().' to w:'.self::$RESIZE_BROWSER_WIDTH_PX.'; h:'.self::$RESIZE_BROWSER_HEIGHT_PX);
                break;
            default:
                break;
        }

        parent::setUp();
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 1/25
     */
    public function testBrandImage(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->assertBrandImageVisible($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 2/25
     */
    public function testAddEntryButtonExists(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                $navbar
                    ->assertVisible(self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN)
                    ->assertSeeIn(self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN, $this->_label_add_entry);
            });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 3/25
     */
    public function testAddTransferButtonExists(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                $navbar
                    ->assertVisible(self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN)
                    ->assertSeeIn(self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN, $this->_label_transfer);
            });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 4/25
     */
    public function testFilterButtonExists(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                $navbar
                    ->assertVisible(self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN)
                    ->assertSeeIn(self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN, $this->_label_filter);
            });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 5/25
     */
    public function testClickProfileDropdown(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                    $navbar
                        ->assertMissing(self::$SELECTOR_NAVBAR_DROPDOWN)
                        ->click(self::$SELECTOR_NAVBAR_PROFILE_LINK)
                        ->pause(self::$WAIT_ONE_TENTH_OF_A_SECOND_IN_MILLISECONDS)
                        ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN)
                        ->with(self::$SELECTOR_NAVBAR_DROPDOWN, function(Browser $navbar_dropdown){
                            $navbar_dropdown->assertSeeIn(self::$SELECTOR_NAVBAR_DROPDOWN_VERSION, $this->_label_version);
                            $this->assertStringContainsString('has-text-info', $navbar_dropdown->attribute(self::$SELECTOR_NAVBAR_DROPDOWN_VERSION, 'class'));
                            $navbar_dropdown->assertDontSeeLink($this->_label_home);
                            $navbar_dropdown->assertSeeLink($this->_label_stats);
                            $navbar_dropdown->assertSeeLink($this->_label_settings);
                            $navbar_dropdown->assertSeeLink($this->_label_logout);
                        });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 6/25
     */
    public function testBurgerMenuVisibleOnSmallerScreenWidthOnHomePage(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                    $navbar
                        ->assertMissing(self::$SELECTOR_NAVBAR_PROFILE_LINK)
                        ->assertMissing(self::$SELECTOR_NAVBAR_DROPDOWN_MENU)
                        ->assertMissing(self::$SELECTOR_NAVBAR_DROPDOWN)
                        ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN_BURGER_MENU)
                        ->assertDontSee($this->_label_add_entry)
                        ->assertDontSee($this->_label_transfer)
                        ->assertDontSee($this->_label_filter)
                        ->click(self::$SELECTOR_NAVBAR_DROPDOWN_BURGER_MENU)
                        ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN_MENU)
                        ->assertSee($this->_label_add_entry)
                        ->assertSee($this->_label_transfer)
                        ->assertSee($this->_label_filter)
                        ->assertDontSee($this->_label_version)
                        ->assertDontSee($this->_label_home)
                        ->assertSeeLink($this->_label_stats)
                        ->assertSeeLink($this->_label_settings)
                        ->assertSeeLink($this->_label_logout);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 7/25
     */
    public function testBrandImageOnStatsPage(){
        $this->browse(function(Browser $browser){
            $browser->visit(new StatsPage());
            $this->assertBrandImageVisible($browser);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 8/25
     */
    public function testAddEntryButtonDoesNotExistOnStatsPage(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                    $navbar->assertDontSee($this->_label_add_entry);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 9/25
     */
    public function testAddTransferButtonDoesNotExistOnStatsPage(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                    $navbar->assertDontSee($this->_label_transfer);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 10/25
     */
    public function testFilterButtonDoesNotExistOnStatsPage(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                    $navbar->assertDontSee($this->_label_filter);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 11/25
     */
    public function testClickProfileDropdownOnStatsPage(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                    $navbar
                        ->assertMissing(self::$SELECTOR_NAVBAR_DROPDOWN)
                        ->click(self::$SELECTOR_NAVBAR_PROFILE_LINK)
                        ->pause(self::$WAIT_ONE_TENTH_OF_A_SECOND_IN_MILLISECONDS)
                        ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN)
                        ->with(self::$SELECTOR_NAVBAR_DROPDOWN, function(Browser $navbar_dropdown){
                            $navbar_dropdown->assertSeeIn(self::$SELECTOR_NAVBAR_DROPDOWN_VERSION, $this->_label_version);
                            $this->assertStringContainsString('has-text-info', $navbar_dropdown->attribute(self::$SELECTOR_NAVBAR_DROPDOWN_VERSION, 'class'));
                            $navbar_dropdown->assertSeeLink($this->_label_home);
                            $navbar_dropdown->assertDontSeeLink($this->_label_stats);
                            $navbar_dropdown->assertSeeLink($this->_label_settings);
                            $navbar_dropdown->assertSeeLink($this->_label_logout);
                        });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-2
     * test 12/25
     */
    public function testBurgerMenuVisibleOnSmallerScreenWidthOnStatsPage(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->within(self::$SELECTOR_NAVBAR, function(Browser $navbar){
                    $navbar
                        ->assertMissing(self::$SELECTOR_NAVBAR_PROFILE_LINK)
                        ->assertMissing(self::$SELECTOR_NAVBAR_DROPDOWN_MENU)
                        ->assertMissing(self::$SELECTOR_NAVBAR_DROPDOWN)
                        ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN_BURGER_MENU)
                        ->assertDontSee($this->_label_add_entry)
                        ->assertDontSee($this->_label_transfer)
                        ->assertDontSee($this->_label_filter)
                        ->click(self::$SELECTOR_NAVBAR_DROPDOWN_BURGER_MENU)
                        ->assertVisible(self::$SELECTOR_NAVBAR_DROPDOWN_MENU)
                        ->assertDontSee($this->_label_add_entry)
                        ->assertDontSee($this->_label_transfer)
                        ->assertDontSee($this->_label_filter)
                        ->assertDontSee($this->_label_version)
                        ->assertSeeLink($this->_label_home)
                        ->assertDontSeeLink($this->_label_stats)
                        ->assertSeeLink($this->_label_settings)
                        ->assertSeeLink($this->_label_logout);
                });
        });
    }

}
