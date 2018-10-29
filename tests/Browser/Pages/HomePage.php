<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class HomePage extends Page {

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
            '@entry-modal'=>'#entry-modal',
        ];
    }

    public function openNewEntryModal(Browser $browser){
        $browser->with('@navbar', function($navbar){
            $navbar->click('#nav-entry-modal');
        });
    }

}