<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SettingsAccountTest extends DuskTestCase {

    /**
     * A Dusk test example.
     * TODO: REMOVE
     *
     * @return void
     */
    public function testExample(){
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel');
        });
    }

    public function testNavigateToAccountsSettings(){
        // TODO: go to /settings
        // TODO: click "Accounts" in sidebar
        // TODO: confirm correct sidebar node is active
        // TODO: confirm correct section is displayed
        // TODO: See section header
    }

    public function testFormFieldsExist(){}

    public function testFormFieldInteraction(){
        // TODO: go to /settings
        // TODO: click "Accounts" in sidebar
        // TODO: field: name
        // TODO: field: institution
        //      wait for is-loading to disappear
        //      select an option
        // TODO: field:
        //      total field only accepts decimal values and converts all other values to decimal
        // TODO: field: currency
        //      select one of the radio buttons
        // TODO: field: active
        //      if creating an inactive/disabled account, clickl otherwise don't click
    }

    public function testFormClearButton(){
        // TODO: go to /settings
        // TODO: click "Accounts" in sidebar
        // TODO: fill in form
        //      see testFormFieldInteraction()
        // TODO: click clear
        // TODO: confirm form is empty/reset to default
    }

    public function testAccountsListedUnderForm(){}

    public function testClickExistingAccountDisplaysDataInForm(){}

    public function testFormClearButtonAfterSelectingListedAccount(){
        // TODO: go to /settings
        // TODO: click "Accounts" in sidebar
        // TODO: wait for list of accounts to populate
        // TODO: randomly select account from list
        // TODO: confirm form is filled in
        // TODO: click clear
        // TODO: confirm form is empty/reset to default
    }

    public function testSaveNewAccount(){
        // TODO: go to /settings
        // TODO: click "Accounts" in sidebar
        // TODO: fill in form
        //      see testFormFieldInteraction()
        // TODO: click save
        // TODO: wait for loading to stop
        // TODO: confirm form is empty
        // TODO: confirm account record is in list
        // TODO: click account record in list
        // TODO: confirm form fields match what was originally entered
    }

    public function testSaveExistingAccount(){
        // TODO: go to /settings
        // TODO: click "Accounts" in sidebar
        // TODO: confirm form is empty
        // TODO: wait for accounts list to be available
        // TODO: click a random active account in list
        // TODO: cofirm form fields have been filled
        // TODO: click account record in list
        // TODO: set account to disabled
        // TODO: change one other property
        // TODO: click save
        // TODO: wait for loading to stop
        // TODO: confirm account record is in list and marked as disabled
        // TODO: click on updated account record in list
        // TODO: confirm form fields match updated values
    }
}
