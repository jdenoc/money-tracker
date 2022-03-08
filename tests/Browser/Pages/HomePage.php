<?php

namespace Tests\Browser\Pages;

use App\Traits\Tests\Dusk\Loading;
use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

class HomePage extends Page {

    use WaitTimes;
    use Loading;

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
            // entry-modal
            '@entry-modal'=>'#entry-modal',
            '@entry-modal-save-btn'=>"#entry-modal button#entry-save-btn",
            '@edit-existing-entry-modal-btn'=>"button.edit-entry-button",
            // transfer-modal
            '@transfer-modal'=>'#transfer-modal',
            '@transfer-modal-save-btn'=>"#transfer-modal button#transfer-save-btn",
            // filter-modal
            '@filter-modal'=>'#filter-modal'
        ];
    }

    public function openExistingEntryModal(Browser $browser, $entry_table_selector){
        $browser
            ->waitFor($entry_table_selector, self::$WAIT_SECONDS)
            ->with($entry_table_selector, function($table_body){
                $table_body->click('@edit-existing-entry-modal-btn');
            });
        $this->waitForLoadingToStop($browser);
        $browser->waitFor('@entry-modal', self::$WAIT_SECONDS);
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

}