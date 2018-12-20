<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class NavbarTest extends DuskTestCase {

    use DatabaseMigrations;

    private $_selector_navbar = '@navbar';
    private $_selector_navbar_brand = ".navbar-brand";
    private $_selector_navbar_dropdown = ".navbar-dropdown";
    private $_selector_profile_link = "#profile-nav-link";

    private $_label_add_entry = "Add Entry";
    private $_label_filter = "Filter";
    private $_label_version = "Version:";
    private $_label_stats = "Statistics";
    private $_label_settings = "Settings";
    private $_label_logout = "Logout";

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

    public function testClickProfileDropdown(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->with($this->_selector_navbar, function($navbar){
                    $navbar_dropdown_selector = $this->_selector_navbar_dropdown;
                    $navbar
                        ->assertMissing($navbar_dropdown_selector)
                        ->click($this->_selector_profile_link)
                        ->assertVisible($navbar_dropdown_selector)
                        ->assertSeeIn($navbar_dropdown_selector, $this->_label_version)
                        ->with($navbar_dropdown_selector, function($navbar_dropdown){
                            $navbar_dropdown->assertSeeLink($this->_label_stats);
                            $navbar_dropdown->assertSeeLink($this->_label_settings);
                            $navbar_dropdown->assertSeeLink($this->_label_logout);
                        });
                });
        });
    }
}
