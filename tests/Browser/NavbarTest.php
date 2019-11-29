<?php

namespace Tests\Browser;

use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;

/**
 * Class NavbarTest
 *
 * @package Tests\Browser
 *
 * @group navigation
 * @group home
 */
class NavbarTest extends DuskTestCase {

    const MOBILE_RESIZE_WIDTH_PX = 1000;
    const RESIZE_HEIGHT_PX = 750;

    private $_selector_navbar = '@navbar';
    private $_selector_navbar_brand = ".navbar-brand";
    private $_selector_navbar_dropdown = ".navbar-dropdown";
    private $_selector_profile_link = "#profile-nav-link";
    private $_selector_navbar_version = "#app-version";

    private $_label_add_entry = "Add Entry";
    private $_label_filter = "Filter";
    private $_label_transfer = "Add Transfer";
    private $_label_version = "Version:";
    private $_label_stats = "Statistics";
    private $_label_settings = "Settings";
    private $_label_logout = "Logout";

    // TODO: test for hamburger menu navbar

    /**
     * @throws \Throwable
     *
     * @group navigation-2
     * test 1/10
     */
    public function testBrandImage(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_navbar, function($navbar){
                    $navbar->assertVisible($this->_selector_navbar_brand);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-2
     * test 2/10
     */
    public function testAddEntryButtonExists(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_navbar, function($navbar){
                    $navbar->assertSee($this->_label_add_entry);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-2
     * test 3/10
     */
    public function testAddTransferButtonExists(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_navbar, function($navbar){
                    $navbar->assertSee($this->_label_transfer);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-2
     * test 4/10
     */
    public function testFilterButtonExists(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_navbar, function($navbar){
                    $navbar->assertSee($this->_label_filter);
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-2
     * test 5/10
     */
    public function testClickProfileDropdown(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_navbar, function(Browser $navbar){
                    $navbar
                        ->assertMissing($this->_selector_navbar_dropdown)
                        ->click($this->_selector_profile_link)
                        ->pause(100)    // 0.1 seconds
                        ->assertVisible($this->_selector_navbar_dropdown)
                        ->with($this->_selector_navbar_dropdown, function(Browser $navbar_dropdown){
                            $navbar_dropdown->assertSeeIn($this->_selector_navbar_version, $this->_label_version);
                            $this->assertContains('has-text-info', $navbar_dropdown->attribute($this->_selector_navbar_version, 'class'));
                            $navbar_dropdown->assertSeeLink($this->_label_stats);
                            $navbar_dropdown->assertSeeLink($this->_label_settings);
                            $navbar_dropdown->assertSeeLink($this->_label_logout);
                        });
                });
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-2
     * test 6/10
     */
    public function testBurgerMenuVisibleOnSmallerScreenWidth(){
        $this->browse(function(Browser $browser){
            $browser
                ->resize(self::MOBILE_RESIZE_WIDTH_PX, self::RESIZE_HEIGHT_PX)
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_navbar, function(Browser $navbar){
                    $navbar
                        ->assertMissing('#profile-nav-link')
                        ->assertMissing('.navbar-menu')
                        ->assertMissing($this->_selector_navbar_dropdown)
                        ->assertVisible('.navbar-burger')
                        ->assertDontSee($this->_label_add_entry)
                        ->assertDontSee($this->_label_transfer)
                        ->assertDontSee($this->_label_filter)
                        ->click('.navbar-burger')
                        ->assertVisible('.navbar-menu')
                        ->assertSee($this->_label_add_entry)
                        ->assertSee($this->_label_transfer)
                        ->assertSee($this->_label_filter)
                        ->assertDontSee($this->_label_version)
                        ->assertSeeLink($this->_label_stats)
                        ->assertSeeLink($this->_label_settings)
                        ->assertSeeLink($this->_label_logout);
                });
        });
    }
}
