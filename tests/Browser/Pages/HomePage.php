<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class HomePage extends Page {

    const WAIT_SECONDS = 10;

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
            '@navbar'=>'.navbar',
            '@new-entry-modal-btn'=>'#nav-entry-modal',
            '@entry-modal'=>'#entry-modal',
            '@edit-existing-entry-modal-btn'=>"button.button.edit-entry-button"
        ];
    }

    public function openNewEntryModal(Browser $browser){
        $browser->with('@navbar', function($navbar){
            $navbar->click('@new-entry-modal-btn');
        })->waitFor('@entry-modal', self::WAIT_SECONDS);
    }

    public function openExistingEntryModal(Browser $browser, $entry_table_selector){
        $browser
            ->waitFor($entry_table_selector, self::WAIT_SECONDS)
            ->with($entry_table_selector, function($table_body){
            $table_body->click('@edit-existing-entry-modal-btn');
        })->waitFor('@entry-modal', self::WAIT_SECONDS);
    }

}