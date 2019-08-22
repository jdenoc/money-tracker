<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

class HomePage extends Page {

    const WAIT_SECOND = 1;
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
        return '/';
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
            '@open-filter-btn'=>'#nav-filter-modal',
            // loading-modal
            '@loading'=>'#loading-modal',
            // entry-modal
            '@entry-modal'=>'#entry-modal',
            '@entry-modal-save-btn'=>"#entry-modal button#entry-save-btn",
            '@edit-existing-entry-modal-btn'=>"button.button.edit-entry-button",
            // transfer-modal
            '@transfer-modal'=>'#transfer-modal',
            '@transfer-modal-save-btn'=>"#transfer-modal button#transfer-save-btn",
            // filter-modal
            '@filter-modal'=>'#filter-modal',
            // notification-modal
            '@notification'=>".snotifyToast",
            '@notification-error'=>".snotifyToast.snotify-error",
            '@notification-info'=>".snotifyToast.snotify-info",
            '@notification-success'=>".snotifyToast.snotify-success",
            '@notification-warning'=>".snotifyToast.snotify-warning",
        ];
    }

    public function waitForLoadingToStop(Browser $browser){
        $browser->waitUntilMissing('@loading', self::WAIT_SECONDS_LONG);
    }

    public function openNewEntryModal(Browser $browser){
        $this->openModalFromNavbar($browser, '@new-entry-modal-btn', '@entry-modal');
    }

    public function openTransferModal(Browser $browser){
        $this->openModalFromNavbar($browser, "@add-transfer-btn", '@transfer-modal');
    }

    public function openFilterModal(Browser $browser){
        $this->openModalFromNavbar($browser, "@open-filter-btn", "@filter-modal");
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
        $this->assertModalSaveButtonIsDisabled($browser, "@entry-modal-save-btn", "entry-modal save button is NOT disabled");
    }

    public function assertTransferModalSaveButtonIsDisabled(Browser $browser){
        $this->assertModalSaveButtonIsDisabled($browser, "@transfer-modal-save-btn", "transfer-modal save button is NOT disabled");
    }

    protected function assertModalSaveButtonIsDisabled(Browser $browser, $modal_save_btn_selector, $fail_message){
        PHPUnit::assertEquals(
            'true',
            $browser->attribute($modal_save_btn_selector, 'disabled'),
            $fail_message
        );
    }

    public function assertEntryModalSaveButtonIsNotDisabled(Browser $browser){
        $this->assertModalSaveButtonIsNotDisabled($browser, "@entry-modal-save-btn", "entry-modal save button IS disabled");
    }

    public function assertTransferModalSaveButtonIsNotDisabled(Browser $browser){
        $this->assertModalSaveButtonIsNotDisabled($browser, "@transfer-modal-save-btn", "transfer-modal save button IS disabled");
    }

    protected function assertModalSaveButtonIsNotDisabled(Browser $browser, $modal_save_btn_selector, $fail_message){
        PHPUnit::assertNotEquals(
            'true',
            $browser->attribute($modal_save_btn_selector, 'disabled'),
            $fail_message
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

    /**
     * @param Browser $browser
     * @param string $element_selector
     * @param string $expected_colour
     * @param string $element_pseudo_selector
     */
    public function assertElementColour(Browser $browser, $element_selector, $expected_colour, $element_pseudo_selector=''){
        $element_hex_colour = $browser->script([
            'css_color = window.getComputedStyle(document.querySelector("'.$element_selector.'"), "'.$element_pseudo_selector.'").getPropertyValue("background-color");',
            'rgb = css_color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);',
            'if(rgb === null){
                hex = css_color;    // assuming that the CSS color returned is HEX
            } else {
                r = (parseInt(rgb[1]) < 16 ? "0" : "")+parseInt(rgb[1]).toString(16);
                g = (parseInt(rgb[2]) < 16 ? "0" : "")+parseInt(rgb[2]).toString(16);
                b = (parseInt(rgb[3]) < 16 ? "0" : "")+parseInt(rgb[3]).toString(16);
                hex = "#"+r+g+b;
            }',
            'return hex;'
        ]);

        PHPUnit::assertEquals(
            strtoupper($expected_colour),
            strtoupper($element_hex_colour[3]),
            "Expected colour [$expected_colour] does not match actual colour [".$element_hex_colour[3]."] of element [$element_selector]"
        );
    }

}