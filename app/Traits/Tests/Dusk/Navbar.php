<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait Navbar {
    use WaitTimes;

    // selectors
    private static string $SELECTOR_NAVBAR = '#navbar';
    private static string $SELECTOR_NAVBAR_BRAND_IMAGE = '#navbar-logo';
    private static string $SELECTOR_NAVBAR_PROFILE = '.profile-picture';
    private static string $SELECTOR_NAVBAR_USERNAME = '.profile-username';
    private static string $SELECTOR_NAVBAR_VERSION = '.app-version';
    private static string $SELECTOR_MODAL_ENTRY = '#entry-modal';
    private static string $SELECTOR_MODAL_TRANSFER = '#transfer-modal';

    // md size screens and above
    private static string $SELECTOR_NAVBAR_NEW_ENTRY_BTN = '#navbar-entry-modal';
    private static string $SELECTOR_NAVBAR_ADD_TRANSFER_BTN = '#navbar-transfer-modal';
    private static string $SELECTOR_NAVBAR_OPEN_FILTER_BTN = '#navbar-filter-modal';
    private static string $SELECTOR_NAVBAR_DROPDOWN_BTN = '#navbar-overflow-menu-btn';
    private static string $SELECTOR_NAVBAR_DROPDOWN = '#navbar-overflow-menu';
    private static string $SELECTOR_NAVBAR_DROPDOWN_MENU = '#navbar-overflow-menu';

    // sm size screens
    private static string $SELECTOR_NAVBAR_SM_SIDEBAR_BTN = '#navbar-sm-sidebar-btn';
    private static string $SELECTOR_NAVBAR_SM_BURGER_BTN = '#navbar-sm-overflow-menu-btn';
    private static string $SELECTOR_NAVBAR_SM_BURGER_MENU = '#navbar-sm-overflow-menu';
    private static string $SELECTOR_NAVBAR_SM_NEW_ENTRY_BTN = '#navbar-sm-entry-modal';
    private static string $SELECTOR_NAVBAR_SM_ADD_TRANSFER_BTN = '#navbar-sm-transfer-modal';
    private static string $SELECTOR_NAVBAR_SM_OPEN_FILTER_BTN = '#navbar-sm-filter-modal';

    public function assertLogoVisible(Browser $navbar) {
        $navbar->assertVisible(self::$SELECTOR_NAVBAR_BRAND_IMAGE);
    }

    public function openNewEntryModal(Browser $navbar) {
        $this->openModalFromNavbar($navbar, self::$SELECTOR_NAVBAR_NEW_ENTRY_BTN, self::$SELECTOR_MODAL_ENTRY);
    }

    public function openTransferModal(Browser $navbar) {
        $this->openModalFromNavbar($navbar, self::$SELECTOR_NAVBAR_ADD_TRANSFER_BTN, self::$SELECTOR_MODAL_TRANSFER);
    }

    public function openFilterModal(Browser $navbar) {
        $this->openModalFromNavbar($navbar, self::$SELECTOR_NAVBAR_OPEN_FILTER_BTN, self::$SELECTOR_MODAL_FILTER);
    }

    public function openModalFromNavbar(Browser $navbar, $selector_btn, $selector_modal) {
        $navbar
            ->click($selector_btn)
            ->waitFor($selector_modal, self::$WAIT_SECONDS);
    }

}
