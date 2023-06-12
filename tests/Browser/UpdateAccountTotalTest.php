<?php

namespace Tests\Browser;

use App\Models\Entry;
use App\Traits\EntryTransferKeys;
use App\Traits\Tests\Dusk\BrowserDateUtil as DuskTraitBrowserDateUtil;
use App\Traits\Tests\Dusk\EntryModal as DustTraitEntryModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use App\Traits\Tests\WaitTimes;
use Brick\Money\Money;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;
use Throwable;

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
    use DuskTraitBrowserDateUtil;
    use DustTraitEntryModal;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use EntryTransferKeys;
    use HomePageSelectors;
    use WaitTimes;

    private $_institution_id;
    private $_account;
    private $_account_type_id;

    public function setUp(): void {
        parent::setUp();

        $institutions = $this->getApiInstitutions();
        $institutions_collection = collect($institutions);
        $this->_institution_id = $institutions_collection->where('active', true)->pluck('id')->random();

        $account = $this->getAccount($this->_institution_id, true);

        $account_types = $this->getApiAccountTypes();
        $account_types_collection = collect($account_types);
        $this->_account_type_id = $account_types_collection->where('disabled', false)->where('account_id', $account['id'])->pluck('id')->random();

        // make sure that at least 1 entry exists for the test
        Entry::factory()->count(1)->create(['entry_date'=>date("Y-m-d"), 'disabled'=>0, 'confirm'=>0, 'account_type_id'=>$this->_account_type_id]);
        // account['total'] will have changed after the new entry is created. need to re-fetch
        $this->_account = $this->getAccount($account['id']);
    }

    public function providerUpdateAccountTotalWithNewEntry(): array {
        return [
            'expense'=>[true],   // test 1/20
            'income'=>[false]    // test 2/20
        ];
    }

    /**
     * @dataProvider providerUpdateAccountTotalWithNewEntry
     * @param bool $is_entry_expense
     *
     * @throws Throwable
     *
     * @group navigation-5
     * test (see provider)/20
     */
    public function testUpdateAccountTotalWithNewEntry(bool $is_entry_expense) {
        $this->browse(function(Browser $browser) use ($is_entry_expense) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            // take note of account total
            $account_total = Money::of($this->_account['total'], $this->_account['currency']);
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $account_total, true);

            // create a new entry
            $entry_total = 10.00;
            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_body, function(Browser $entry_modal_body) use ($is_entry_expense, $entry_total) {
                    // The date field should already be filled in. No need to fill it in again.
                    $entry_modal_body->type($this->_selector_modal_entry_field_value, $entry_total);
                    $this->waitUntilSelectLoadingIsMissing($entry_modal_body, $this->_selector_modal_entry_field_account_type);
                    $entry_modal_body
                        ->select($this->_selector_modal_entry_field_account_type, $this->_account_type_id)
                        ->type($this->_selector_modal_entry_field_memo, "Test new entry account total update");

                    if (!$is_entry_expense) {
                        // entry is expense by default, so we only need to do something when we want to mark it as an income entry
                        $entry_modal_body->click($this->_selector_modal_entry_field_expense);
                    }
                    $entry_modal_body->click($this->_selector_modal_entry_field_date);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);

            // confirm account total updated
            $new_account_total = $account_total->plus(
                Money::of($entry_total, $this->_account['currency'])
                    ->multipliedBy(($is_entry_expense ? -1 : 1))
            );
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-5
     * test 3/20
     */
    public function testUpdateAccountTotalWithExistingEntryByTogglingIncomeExpense() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            // take note of account total
            $account_total = Money::of($this->_account['total'], $this->_account['currency']);
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $account_total, true);

            // update an existing entry
            $entry = $this->getEntry($this->_account_type_id);
            $entry_selector = sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry['id']);

            $switch_text = '';
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $entry_modal_body) use ($entry_selector, &$switch_text) {
                    $entry_modal_body
                        ->click($this->_selector_modal_entry_field_expense)
                        ->pause(self::$WAIT_HALF_SECOND_IN_MILLISECONDS); // need to wait for the transition to complete after click;
                    $switch_text = $entry_modal_body->text($this->_selector_modal_entry_field_expense);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);

            // confirm account total updated
            $is_expense = strtolower(trim($switch_text)) == 'expense';
            $entry_value = Money::of($entry['entry_value'], $this->_account['currency']);
            $new_account_total = $account_total
                ->minus($entry_value->multipliedBy($is_expense ? 1 : -1))   // remove previous value
                ->plus($entry_value->multipliedBy($is_expense ? -1 : 1));   // add new value
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-5
     * test 4/20
     */
    public function testUpdateAccountTotalWithExistingEntryByChangingValue() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            // take note of account total
            $account_total = Money::of($this->_account['total'], $this->_account['currency']);
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $account_total, true);

            // update an existing entry
            $entry = $this->getEntry($this->_account_type_id);
            $entry_selector = sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry['id']);

            $new_value = 10.00;
            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_body, function(Browser $entry_modal_body) use ($entry_selector, $new_value) {
                    $entry_modal_body->clear($this->_selector_modal_entry_field_value);
                    $entry_modal_body->type($this->_selector_modal_entry_field_value, $new_value);
                })
                ->within($this->_selector_modal_foot, function(Browser $modal_foot) {
                    $modal_foot->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);

            // confirm account total updated
            $expense_multiplier = $entry['expense'] == 1 ? -1 : 1;
            $new_account_total = $account_total
                ->minus(Money::of($entry['entry_value'], $this->_account['currency'])->multipliedBy($expense_multiplier))
                ->plus(Money::of($new_value, $this->_account['currency'])->multipliedBy($expense_multiplier));
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-5
     * test 5/20
     */
    public function testOpenExistingEntryAndDeleteIt() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            // take note of account total
            $account_total = Money::of($this->_account['total'], $this->_account['currency']);
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $account_total, true);

            // delete an existing entry
            $entry = $this->getEntry($this->_account_type_id);
            $entry_selector = sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry['id']);

            $browser
                ->openExistingEntryModal($entry_selector)
                ->within($this->_selector_modal_entry, function(Browser $entry_modal) {
                    $entry_modal
                        ->assertVisible($this->_selector_modal_entry_btn_delete)
                        ->click($this->_selector_modal_entry_btn_delete);
                });
            $this->waitForLoadingToStop($browser);
            $browser->waitUntilMissing($this->_selector_modal_entry, self::$WAIT_SECONDS);

            // confirm account total updated
            $new_account_total = $account_total
                ->minus(Money::of($entry['entry_value'], $this->_account['currency'])->multipliedBy(($entry['expense'] == 1 ? -1 : 1)));
            $this->assertAccountTotalInBrowser($browser, $this->_institution_id, $this->_account['id'], $new_account_total);
        });
    }

    public function providerUpdateAccountTotalsWithNewTransferEntries(): array {
        return [
            // [$is_from_account_external, $is_to_account_external]
            'neither account is external'=>[false, false],  // test 6/20
            '"to" account is external'=>[false, true],      // test 7/20
            '"from" account is external'=>[true, false]     // test 8/20
            // there will NEVER be a [true, true] option. there can not be two "external" accounts
        ];
    }

    /**
     * @dataProvider providerUpdateAccountTotalsWithNewTransferEntries
     *
     * @group navigation-5
     * test (see provider)/20
     */
    public function testUpdateAccountTotalsWithNewTransferEntries(bool $is_from_account_external, bool $is_to_account_external) {
        $account = [];
        if (!$is_from_account_external && !$is_to_account_external) {
            $account['from'] = $this->_account;
            $account['from']['account_type_id'] = $this->_account_type_id;

            do {
                $account_types = $this->getApiAccountTypes();
                $account_types_collection = collect($account_types);
                $account_type = $account_types_collection
                    ->where('disabled', false)
                    ->where('account_id', '!=', $account['from']['id'])
                    ->random();
                $account['to'] = $this->getAccount($account_type['account_id']);
            } while (!$account['to']['active']);
            $account['to']['account_type_id'] = $account_type['id'];
        } elseif ($is_to_account_external) {
            $account['to']['id'] = 0;
            $account['to']['account_type_id'] = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $account['from'] = $this->_account;
            $account['from']['account_type_id'] = $this->_account_type_id;
        } elseif ($is_from_account_external) {
            $account['from']['id'] = 0;
            $account['from']['account_type_id'] = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $account['to'] = $this->_account;
            $account['to']['account_type_id'] = $this->_account_type_id;
        }

        $this->browse(function(Browser $browser) use ($is_from_account_external, $is_to_account_external, $account) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            // take note of "to" account total
            if (!$is_to_account_external) {
                $account_to_total = Money::of($account['to']['total'], $account['to']['currency']);
                $this->assertAccountTotalInBrowser($browser, $account['to']['institution_id'], $account['to']['id'], $account_to_total, true);
            }

            // take note of the "from" account total
            if (!$is_from_account_external) {
                if ($is_to_account_external) {
                    $init_institution = true;
                } elseif ($account['to']['institution_id'] == $account['from']['institution_id']) {
                    $init_institution = false;
                } else {
                    $init_institution = true;
                }
                $account_from_total = Money::of($account['from']['total'], $account['from']['currency']);
                $this->assertAccountTotalInBrowser($browser, $account['from']['institution_id'], $account['from']['id'], $account_from_total, $init_institution);
            }

            // generate some test values
            $transfer_entry_data = [
                'memo'=>"Test transfer - save - ".fake()->uuid,
                'value'=>fake()->randomFloat(2, 0, 100),
                'from_account_type_id'=>($account['from']['account_type_id']),
                'to_account_type_id'=>($account['to']['account_type_id']),
            ];
            // get locale date string from browser
            $browser_locale_date = $this->getBrowserLocaleDate($browser);
            $browser_locale_date_for_typing = $this->processLocaleDateForTyping($browser_locale_date);

            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($transfer_entry_data, $browser_locale_date_for_typing) {
                    $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_from);
                    $this->waitUntilSelectLoadingIsMissing($modal, $this->_selector_modal_transfer_field_to);
                    $modal
                        ->type($this->_selector_modal_transfer_field_date, $browser_locale_date_for_typing)
                        ->type($this->_selector_modal_transfer_field_value, $transfer_entry_data['value'])
                        ->select($this->_selector_modal_transfer_field_from, $transfer_entry_data['from_account_type_id'])
                        ->select($this->_selector_modal_transfer_field_to, $transfer_entry_data['to_account_type_id'])
                        ->type($this->_selector_modal_transfer_field_memo, $transfer_entry_data['memo']);
                })
                ->within($this->_selector_modal_transfer, function(Browser $modal) {
                    $modal->click($this->_selector_modal_transfer_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser->assertMissing($this->_selector_modal_transfer);

            if (!$is_from_account_external) {
                // considered expense
                $new_account_total = $account_from_total
                    ->plus(Money::of($transfer_entry_data['value'], $account['from']['currency'])->multipliedBy(-1));
                $this->assertAccountTotalInBrowser($browser, $account['from']['institution_id'], $account['from']['id'], $new_account_total);
            }
            if (!$is_to_account_external) {
                // considered income
                $new_account_total = $account_to_total
                    ->plus(Money::of($transfer_entry_data['value'], $account['to']['currency'])->multipliedBy(1));
                $this->assertAccountTotalInBrowser($browser, $account['to']['institution_id'], $account['to']['id'], $new_account_total);
            }
        });
    }

    private function assertAccountTotalInBrowser(Browser $browser, int $institution_id, int $account_id, Money $expected_account_total, bool $init=false): void {
        $browser->within($this->_selector_panel_institutions.' #institution-'.$institution_id, function(Browser $institution_node) use ($init, $account_id, $expected_account_total) {
            // ONLY click on the institution node if this is at start up
            // OTHERWISE the accounts should already be visible
            if ($init) {
                $institution_node
                    // click institution node;
                    ->click('div')
                    ->pause(self::$WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS)
                    ->assertVisible($this->_selector_panel_institutions_accounts);
            }

            $institution_node->within($this->_selector_panel_institutions_accounts.' #account-'.$account_id, function(Browser $account_node) use ($expected_account_total) {
                $account_node_total = $account_node->text($this->_selector_panel_institutions_accounts_account_total);
                $this->assertTrue($expected_account_total->isEqualTo($account_node_total));
            });
        });
    }

    /**
     * @param int $id
     * @param bool $is_institution_id
     * @return array
     */
    private function getAccount(int $id, bool $is_institution_id=false) {
        $accounts = $this->getApiAccounts();
        $accounts_collection = collect($accounts);
        if ($is_institution_id) {
            return $accounts_collection->where('active', true)->where('institution_id', $id)->random();
        } else {
            return $accounts_collection->where('id', $id)->first();
        }
    }

    /**
     * @param int $account_type_id
     * @return array
     */
    private function getEntry(int $account_type_id) {
        $entries = $this->getApiEntries();
        unset($entries['count']);
        $entries_collection = collect($entries);
        return $entries_collection->where('account_type_id', $account_type_id)->where('confirm', 0)->random();
    }

}
