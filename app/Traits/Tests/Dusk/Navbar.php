<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait Navbar {

    use WaitTimes;

    private static string $SELECTOR_NAVBAR = '.navbar';
    private static string $SELECTOR_NAVBAR_BRAND_IMAGE = '.navbar-brand';
    private static string $SELECTOR_NAVBAR_NEW_ENTRY_BTN = '#nav-entry-modal';
    private static string $SELECTOR_NAVBAR_ADD_TRANSFER_BTN = '#nav-transfer-modal';
    private static string $SELECTOR_NAVBAR_OPEN_FILTER_BTN = '#nav-filter-modal';
    private static string $SELECTOR_NAVBAR_PROFILE_LINK = '#profile-nav-link';
    private static string $SELECTOR_NAVBAR_DROPDOWN = '.navbar-dropdown';
    private static string $SELECTOR_NAVBAR_DROPDOWN_BURGER_MENU = '.navbar-burger';
    private static string $SELECTOR_NAVBAR_DROPDOWN_VERSION = '#app-version';
    private static string $SELECTOR_NAVBAR_DROPDOWN_MENU = '.navbar-menu';
    private static string $SELECTOR_MODAL_ENTRY = '#entry-modal';
    private static string $SELECTOR_MODAL_TRANSFER = '#transfer-modal';
    private static string $SELECTOR_MODAL_FILTER = '#filter-modal';

    /**
     * @param Browser $navbar
     */
    public function assertBrandImageVisible(Browser $navbar){
        $navbar->assertVisible(self::$SELECTOR_NAVBAR_BRAND_IMAGE);
    }

    public function openNewEntryModal(Browser $navbar){
        $this->openModalFromNavbar($navbar, self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN, self::$SELECTOR_MODAL_ENTRY);
    }

    public function openTransferModal(Browser $navbar){
        $this->openModalFromNavbar($navbar, self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN, self::$SELECTOR_MODAL_TRANSFER);
    }

    public function openFilterModal(Browser $navbar){
        $this->openModalFromNavbar($navbar, self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN, self::$SELECTOR_MODAL_FILTER);
    }

    public function openModalFromNavbar(Browser $navbar, $selector_btn, $selector_modal){
        $navbar
            ->click($selector_btn)
            ->waitFor($selector_modal, self::$WAIT_SECONDS);
    }
}
