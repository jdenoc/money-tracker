<?php

namespace Tests\Browser;

use App\Entry;
use App\Http\Controllers\Api\EntryController;
use Faker\Factory as FakerFactory;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;

/**
 * Class UpdateAccountTotalTest
 *
 * @package Tests\Browser
 *
 * @group navigation
 * @group modal
 * @group home
 */
class UpdateAccountTotalTest extends DuskTestCase {

    use HomePageSelectors;

    private $_institution_id;
    private $_account;
    private $_account_type_id;

    public function setUp(){
        parent::setUp();

        $institutions = $this->getApiInstitutions();
        $institutions_collection = collect($institutions);
        $this->_institution_id = $institutions_collection->where('active', true)->pluck('id')->random();

        $account = $this->getAccount($this->_institution_id, true);

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
           'expense'=>[true],   // test 1/25
           'income'=>[false]    // test 2/25
       ] ;
    }

    /**
     * @dataProvider providerUpdateAccountTotalWithNewEntry
     * @param bool $is_entry_expense
     *
     * @throws \Throwable
     *
     * @group navigation-4
     * test (see provider)/25
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

    /**
     * @throws \Throwable
     *
     * @group navigation-4
     * test 3/25
     */
    public function testUpdateAccountTotalWithExistingEntryByTogglingIncomeExpense(){
        $this->browse(function (Browser $browser){
            $browser->visit(new HomePage())->waitForLoadingToStop();

            // take note of account total
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $this->_account['total'], true);

            // update an existing entry
            $entry = $this->getEntry($this->_account_type_id);
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

    /**
     * @throws \Throwable
     *
     * @group navigation-4
     * test 4/25
     */
    public function testUpdateAccountTotalWithExistingEntryByChangingValue(){
        $this->browse(function (Browser $browser){
            $browser->visit(new HomePage())->waitForLoadingToStop();

            // take note of account total
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $this->_account['total'], true);

            // update an existing entry
            $entry = $this->getEntry($this->_account_type_id);
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
     * @throws \Throwable
     *
     * @group navigation-4
     * test 5/25
     */
    public function testOpenExistingEntryAndDeleteIt(){
        $this->browse(function(Browser $browser){
            $browser->visit(new HomePage())->waitForLoadingToStop();

            // take note of account total
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $this->_account['total'], true);

            // delete an existing entry
            $entry = $this->getEntry($this->_account_type_id);
            $entry_selector = "#entry-".$entry['id'];

            $browser
                ->openExistingEntryModal($entry_selector)
                ->with($this->_selector_modal_entry, function($entry_modal){
                    $entry_modal
                        ->assertVisible($this->_selector_modal_entry_btn_delete)
                        ->click($this->_selector_modal_entry_btn_delete);
                })
                ->waitForLoadingToStop()
                ->waitUntilMissing($this->_selector_modal_entry, HomePage::WAIT_SECONDS)
            ;

            // confirm account total updated
            $new_account_total = $this->_account['total'] - ($entry['expense'] == 1 ?-1:1)*$entry['entry_value'];
            $this->assertAccountTotal($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    public function providerUpdateAccountTotalsWithNewTransferEntries(){
        return [
            // [$is_from_account_external, $is_to_account_external]
            'neither account is external'=>[false, false],  // test 6/25
            '"to" account is external'=>[false, true],      // test 7/25
            '"from" account is external'=>[true, false]     // test 8/25
            // there will NEVER be a [true, true] option. there can not be two "external" accounts
        ];
    }

    /**
     * @dataProvider providerUpdateAccountTotalsWithNewTransferEntries
     * @param bool $is_from_account_external
     * @param bool $is_to_account_external
     *
     * @throws \Throwable
     *
     * @group navigation-4
     * test (see provider)/25
     */
    public function testUpdateAccountTotalsWithNewTransferEntries($is_from_account_external, $is_to_account_external){
        $account = [];
        if(!$is_from_account_external && !$is_to_account_external){
            $account['from'] = $this->_account;
            $account['from']['account_type_id'] = $this->_account_type_id;

            do{
                $account_types = $this->getApiAccountTypes();
                $account_types_collection = collect($account_types);
                $account_type = $account_types_collection
                    ->where('disabled', false)
                    ->where('account_id','!=', $account['from']['id'])
                    ->random();
                $account['to'] = $this->getAccount($account_type['account_id']);
            } while($account['to']['disabled']);
            $account['to']['account_type_id'] = $account_type['id'];

        } elseif($is_to_account_external){
            $account['to']['id'] = 0;
            $account['to']['account_type_id'] = EntryController::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $account['from'] = $this->_account;
            $account['from']['account_type_id'] = $this->_account_type_id;
        } elseif($is_from_account_external){
            $account['from']['id'] = 0;
            $account['from']['account_type_id'] = EntryController::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $account['to'] = $this->_account;
            $account['to']['account_type_id'] = $this->_account_type_id;
        }

        $this->browse(function(Browser $browser) use ($is_from_account_external, $is_to_account_external, $account){
            $browser->visit(new HomePage())->waitForLoadingToStop();

            // take note of "to" account total
            if(!$is_to_account_external){
                $this->assertAccountTotal($browser, $account['to']['institution_id'], $account['to']['id'], $account['to']['total'], true);
            }

            // take note of the "from" account total
            if(!$is_from_account_external){
                if($is_to_account_external){
                    $init_institution = true;
                } elseif($account['to']['institution_id'] == $account['from']['institution_id']) {
                    $init_institution = false;
                } else {
                    $init_institution = true;
                }
                $this->assertAccountTotal($browser, $account['from']['institution_id'], $account['from']['id'], $account['from']['total'], $init_institution);
            }

            // generate some test values
            $faker = FakerFactory::create();
            $transfer_entry_data = [
                'memo'=>"Test transfer - save - ".$faker->uuid,
                'value'=>$faker->randomFloat(2, 0, 100),
                'from_account_type_id'=>($account['from']['account_type_id']),
                'to_account_type_id'=>($account['to']['account_type_id']),
            ];
            // get locale date string from browser
            $browser_locale_date = $browser->getBrowserLocaleDate();
            $browser_locale_date_for_typing = $browser->processLocaleDateForTyping($browser_locale_date);

            $browser->openTransferModal()
                ->with($this->_selector_modal_transfer, function(Browser $modal) use ($transfer_entry_data, $browser_locale_date_for_typing){
                    $modal
                        ->type($this->_selector_modal_transfer_field_date, $browser_locale_date_for_typing)
                        ->type($this->_selector_modal_transfer_field_value, $transfer_entry_data['value'])
                        ->waitUntilMissing($this->_selector_modal_transfer_field_from_is_loading)
                        ->select($this->_selector_modal_transfer_field_from, $transfer_entry_data['from_account_type_id'])
                        ->waitUntilMissing($this->_selector_modal_transfer_field_to_is_loading)
                        ->select($this->_selector_modal_transfer_field_to, $transfer_entry_data['to_account_type_id'])
                        ->type($this->_selector_modal_transfer_field_memo, $transfer_entry_data['memo']);
                })
                ->with($this->_selector_modal_transfer, function(Browser $modal){
                    $modal->click($this->_selector_modal_transfer_btn_save);
                })
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_modal_transfer);

            if(!$is_from_account_external){
                // considered expense
                $new_account_total = $account['from']['total']+(-1*$transfer_entry_data['value']);
                $this->assertAccountTotal($browser, $account['from']['institution_id'], $account['from']['id'], $new_account_total);
            }
            if(!$is_to_account_external){
                // considered income
                $new_account_total = $account['to']['total']+(1*$transfer_entry_data['value']);
                $this->assertAccountTotal($browser, $account['to']['institution_id'], $account['to']['id'], $new_account_total);
            }
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

    /**
     * @param int $id
     * @param bool $is_institution_id
     * @return array
     */
    private function getAccount($id, $is_institution_id=false){
        $accounts = $this->getApiAccounts();
        $accounts_collection = collect($accounts);
        if($is_institution_id){
            return $accounts_collection->where('disabled', false)->where('institution_id', $id)->random();
        } else {
            return $accounts_collection->where('id', $id)->first();
        }
    }

    /**
     * @param int $account_type_id
     * @return array
     */
    private function getEntry($account_type_id){
        $entries = $this->getApiEntries();
        unset($entries['count']);
        $entries_collection = collect($entries);
        return $entries_collection->where('account_type_id', $account_type_id)->where('confirm', 0)->random();
    }

}
