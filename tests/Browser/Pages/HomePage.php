<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

class HomePage extends Page {

    const WAIT_SECONDS = 10;
    const WAIT_SECONDS_LONG = 30;

    const NOTIFICATION_ERROR = 'error';
    const NOTIFICATION_INFO = 'info';
    const NOTIFICATION_SUCCESS = 'success';
    const NOTIFICATION_WARNING = 'warning';

    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url(){
        return '/vue-mock';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser){
        //
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements(){
        return [
            // navbar
            '@navbar'=>'.navbar',
            '@new-entry-modal-btn'=>'#nav-entry-modal',
            '@add-transfer-btn'=>'#nav-transfer-modal',
            // loading-modal
            '@loading'=>'#loading-modal',
            // entry-modal
            '@entry-modal'=>'#entry-modal',
            '@entry-modal-save-btn'=>"#entry-modal button#entry-save-btn",
            '@edit-existing-entry-modal-btn'=>"button.button.edit-entry-button",
            // transfer-modal
            '@transfer-modal'=>'#transfer-modal',
            '@transfer-modal-save-btn'=>"#transfer-modal button#transfer-save-btn",
            // notification-modal
            '@notification'=>".snotifyToast",
            '@notification-error'=>".snotifyToast.snotify-error",
            '@notification-info'=>".snotifyToast.snotify-info",
            '@notification-success'=>".snotifyToast.snotify-success",
            '@notification-warning'=>".snotifyToast.snotify-warning", // TODO: confirm this is correct
        ];
    }

    public function waitForLoadingToStop(Browser $browser){
        $browser
            ->assertVisible('@loading')
            ->waitUntilMissing('@loading', self::WAIT_SECONDS_LONG);
    }

    public function openNewEntryModal(Browser $browser){
        $this->openModalFromNavbar($browser, '@new-entry-modal-btn', '@entry-modal');
    }

    public function openTransferModal(Browser $browser){
        $this->openModalFromNavbar($browser, "@add-transfer-btn", '@transfer-modal');
    }

    public function openModalFromNavbar(Browser $browser, $selector_btn, $selector_modal){
        $browser->with('@navbar', function($navbar) use ($selector_btn){
            $navbar->click($selector_btn);
        })->waitFor($selector_modal, self::WAIT_SECONDS);
    }

    public function openExistingEntryModal(Browser $browser, $entry_table_selector){
        $browser
            ->waitFor($entry_table_selector, self::WAIT_SECONDS)
            ->with($entry_table_selector, function($table_body){
                $table_body->click('@edit-existing-entry-modal-btn');
            })
            ->waitForLoadingToStop()
            ->waitFor('@entry-modal', self::WAIT_SECONDS);
    }

    public function assertEntryModalSaveButtonIsDisabled(Browser $browser){
        PHPUnit::assertEquals(
            'true',
            $browser->attribute("@entry-modal-save-btn", 'disabled'),
            "Entry-modal save button is NOT disabled"
        );
    }

    public function assertTransferModalSaveButtonIsDisabled(Browser $browser){
        PHPUnit::assertEquals(
            'true',
            $browser->attribute("@transfer-modal-save-btn", 'disabled'),
            "transfer-modal save button is NOT disabled"
        );
    }

    public function assertEntryModalSaveButtonIsNotDisabled(Browser $browser){
        PHPUnit::assertNotEquals(
            'true',
            $browser->attribute("@entry-modal-save-btn", 'disabled'),
            "Entry-modal save button IS disabled"
        );
    }

    public function assertTransferModalSaveButtonIsNotDisabled(Browser $browser){
        PHPUnit::assertNotEquals(
            'true',
            $browser->attribute("@transfer-modal-save-btn", 'disabled'),
            "transfer-modal save button IS disabled"
        );
    }

    public function assertNotification(Browser $browser, $notification_type, $notification_message){
        switch($notification_type){
            case self::NOTIFICATION_ERROR:
                $notification_type_selector = '@notification-error';
                break;
            case self::NOTIFICATION_INFO:
            default:
                $notification_type_selector = '@notification-info';
                break;
            case self::NOTIFICATION_SUCCESS:
                $notification_type_selector = '@notification-success';
                break;
            case self::NOTIFICATION_WARNING:
                $notification_type_selector = '@notification-warning';
                break;
        }

        $browser
            ->waitFor('@notification', self::WAIT_SECONDS)
            ->assertVisible($notification_type_selector)
            ->assertSee($notification_message)
            // Selenium has issues on some tests.
            // We need to mouse over the navbar to make sure that notification continues its progress of dismissal.
            ->mouseover('@navbar')
            ->waitUntilMissing($notification_type_selector, self::WAIT_SECONDS)
            ->pause(250);    // give the element another 0.25 seconds to fully disappear;
    }

}