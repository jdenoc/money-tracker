<?php

namespace Tests\Browser;

use App\Entry;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\HomePageSelectors;

/**
 * Class UpdateAccountTotalTest
 *
 * @package Tests\Browser
 *
 * @group entry-modal
 * @group modal
 * @group home
 */
class UpdateAccountTotalTest extends DuskTestCase {

    use DatabaseMigrations;
    use HomePageSelectors;

    private $_institution_id;
    private $_account;
    private $_account_type_id;

    public function setUp(){
        parent::setUp();

        $institutions = $this->getApiInstitutions();
        $institutions_collection = collect($institutions);
        $this->_institution_id = $institutions_collection->where('active', true)->pluck('id')->random();

        $account = $this->getAccount();

        $account_types = $this->getApiAccountTypes();
        $account_types_collection = collect($account_types);
        $this->_account_type_id = $account_types_collection->where('disabled', false)->where('account_id', $account['id'])->pluck('id')->random();

        // make sure that at least 1 entry exists for the test
        factory(Entry::class, 1)->create(['entry_date'=>date("Y-m-d"), 'disabled'=>0, 'confirm'=>0, 'account_type_id'=>$this->_account_type_id]);
        // account['total'] will have changed after the new entry is created. need to re-fetch
        $this->_account = $this->getAccount($account['id']);
    }

    public function providerUpdateAccountTotalWithNewEntry(){
       return [
           'expense'=>[true],
           'income'=>[false]
       ] ;
    }

    /**
     * @dataProvider providerUpdateAccountTotalWithNewEntry
     * @param bool $is_entry_expense
     *
     * @throws \Throwable
     */
    public function testUpdateAccountTotalWithNewEntry($is_entry_expense){
        $this->browse(function (Browser $browser) use($is_entry_expense){
            $browser->visit(new HomePage())->waitForLoadingToStop();

            // take note of account total
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $this->_account['total'], true);

            // create a new entry
            $entry_total = 10.00;
            $browser->openNewEntryModal()
                ->with($this->_selector_modal_body, function(Browser $entry_modal_body) use ($is_entry_expense, $entry_total){
                    // The date field should already be filled in. No need to fill it in again.
                    $entry_modal_body
                        ->type($this->_selector_modal_entry_field_value, $entry_total)
                        ->waitUntilMissing($this->_selector_modal_entry_field_account_type_is_loading, HomePage::WAIT_SECONDS)
                        ->select($this->_selector_modal_entry_field_account_type, $this->_account_type_id)
                        ->type($this->_selector_modal_entry_field_memo, "Test new entry account total update");

                    if(!$is_entry_expense){
                        // entry is expense by default, so we only need to do something when we want to mark it as an income entry
                        $entry_modal_body->click($this->_selector_modal_entry_field_expense);
                    }
                    $entry_modal_body->click($this->_selector_modal_entry_field_date);

                })
                ->with($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop();

            // confirm account total updated
            $new_account_total = $this->_account['total']+($is_entry_expense?-1:1)*$entry_total;
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    public function testUpdateAccountTotalWithExistingEntryByTogglingIncomeExpense(){
        $this->browse(function (Browser $browser){
            $browser->visit(new HomePage())->waitForLoadingToStop();

            // take note of account total
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $this->_account['total'], true);

            // update an existing entry
            $entry = $this->getEntry();
            $entry_selector = "#entry-".$entry['id'];

            $switch_text = '';
            $browser
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function(Browser $entry_modal_body) use ($entry_selector, &$switch_text){
                    $entry_modal_body
                        ->click($this->_selector_modal_entry_field_expense)
                        ->pause(500); // 0.5 seconds - need to wait for the transition to complete after click;
                    $switch_text = $entry_modal_body->text($this->_selector_modal_entry_field_expense);
                })
                ->with($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop();

            // confirm account total updated
            $new_account_total = $this->_account['total']
                -(strtolower(trim($switch_text)) == 'expense'?1:-1)*$entry['entry_value']
                +(strtolower(trim($switch_text)) == 'expense'?-1:1)*$entry['entry_value'];
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    public function testUpdateAccountTotalWithExistingEntryByChangingValue(){
        $this->browse(function (Browser $browser){
            $browser->visit(new HomePage())->waitForLoadingToStop();

            // take note of account total
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $this->_account['total'], true);

            // update an existing entry
            $entry = $this->getEntry();
            $entry_selector = "#entry-".$entry['id'];

            $new_value = 10.00;
            $browser
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_body, function(Browser $entry_modal_body) use ($entry_selector, $new_value){
                    $entry_modal_body->clear($this->_selector_modal_entry_field_value);
                    $entry_modal_body->type($this->_selector_modal_entry_field_value, $new_value);
                })
                ->with($this->_selector_modal_foot, function(Browser $modal_foot){
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop();

            // confirm account total updated
            $new_account_total = $this->_account['total']
                -($entry['expense'] == 1 ?-1:1)*$entry['entry_value']
                +($entry['expense'] == 1 ?-1:1)*$new_value;
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    /**
     * @param Browser $browser
     * @param int $institution_id
     * @param int $account_id
     * @param float $account_total
     * @param bool $init
     */
    private function assertAccountTotal(Browser $browser, $institution_id, $account_id, $account_total, $init=false){
        $browser->with($this->_selector_panel_institutions.' #institution-'.$institution_id, function(Browser $institution_node) use ($init, $account_id, $account_total){
            // ONLY click on the institution node if this is at start up
            // OTHERWISE the accounts should already be visible
            if($init){
                $institution_node
                    // click institution node;
                    ->click('')
                    ->pause(400)    // 0.4 seconds
                    ->assertVisible($this->_selector_panel_institutions_accounts);
            }

            $institution_node->with($this->_selector_panel_institutions_accounts.' #account-'.$account_id, function(Browser $account_node) use ($account_total){
                $account_node_total = $account_node->text($this->_selector_panel_institutions_accounts_account_name.' span.account-currency span');
                $this->assertEquals($account_total, $account_node_total);
            });
        });
    }

    private function getAccount($id=null){
        $accounts = $this->getApiAccounts();
        $accounts_collection = collect($accounts);
        if(is_null($id)){
            return $accounts_collection->where('disabled', false)->where('institution_id', $this->_institution_id)->random();
        } else {
            return $accounts_collection->where('id', $id)->first();
        }
    }

    private function getEntry(){
        $entries = $this->getApiEntries();
        unset($entries['count']);
        $entries_collection = collect($entries);
        return $entries_collection->where('account_type_id', $this->_account_type_id)->random();
    }

}
