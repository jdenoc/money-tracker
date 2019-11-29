<?php

namespace Tests\Browser;

use App\Entry;
use App\Http\Controllers\Api\EntryController;
use Illuminate\Support\Facades\DB;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;

/**
 * Class PaginationTest
 *
 * @package Tests\Browser
 *
 * @group navigation
 * @group home
 */
class PaginationTest extends DuskTestCase {

    use HomePageSelectors;

    const PAGE_NUMBER_ZERO = 0;
    const PAGE_NUMBER_ONE = 1;
    const PAGE_NUMBER_TWO = 2;

    const ENTRY_COUNT_ONE = 10;                                                 // number of entries needed to be generated for 1 page of results
    const ENTRY_COUNT_TWO = (EntryController::MAX_ENTRIES_IN_RESPONSE*2)-10;    // number of entries needed to be generated for 2 pages of results
    const ENTRY_COUNT_THREE = (EntryController::MAX_ENTRIES_IN_RESPONSE*3)-25;  // number of entries needed to be generated for 3 pages of results

    private $_account_types = [];

    public function setUp(){
        parent::setUp();
        // clear the `entries` table, so we can test against an exact amount of entries.
        DB::statement("TRUNCATE entries");
        $this->_account_types = $this->getApiAccountTypes();
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-3
     * test 1/10
     */
    public function testPaginationButtonsNotVisibleIfEntryCountLessThanPageMax(){
        factory(Entry::class, self::ENTRY_COUNT_ONE)->create($this->entryOverrideAttributes());
        $entries = $this->getApiEntries();
        $this->assertLessThan(EntryController::MAX_ENTRIES_IN_RESPONSE, $entries['count']);

        $this->browse(function (Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_pagination_btn_next)
                ->assertMissing($this->_selector_pagination_btn_prev);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-3
     * test 2/10
     */
    public function testNextPaginationButtonVisibleIfEntryCountGreaterThanPageMax(){
        factory(Entry::class, self::ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries = $this->getApiEntries();
        $this->assertGreaterThanOrEqual(EntryController::MAX_ENTRIES_IN_RESPONSE, $entries['count']);

        $this->browse(function (Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertMissing($this->_selector_pagination_btn_prev)
                ->assertVisible($this->_selector_pagination_btn_next);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-3
     * test 3/10
     */
    public function testPreviousPaginationButtonsVisiblePagedToNextPage(){
        factory(Entry::class, self::ENTRY_COUNT_THREE)->create($this->entryOverrideAttributes());
        $entries_page1 = $this->getApiEntries(self::PAGE_NUMBER_ONE);
        $entries_page2 = $this->getApiEntries(self::PAGE_NUMBER_TWO);

        $this->assertEquals($entries_page1['count'], $entries_page2['count']);
        $this->assertGreaterThanOrEqual(EntryController::MAX_ENTRIES_IN_RESPONSE*2, $entries_page1['count']);
        $this->assertLessThan(EntryController::MAX_ENTRIES_IN_RESPONSE*3, $entries_page2['count']);

        $this->browse(function (Browser $browser) use ($entries_page1, $entries_page2){
            unset($entries_page1['count'], $entries_page2['count']);
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_pagination_btn_next)
                ->click($this->_selector_pagination_btn_next)
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_pagination_btn_prev);
            $this->assertEntriesDisplayed($browser, $entries_page1);

            $browser
                ->assertVisible($this->_selector_pagination_btn_next)
                ->click($this->_selector_pagination_btn_next)
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_pagination_btn_prev)
                ->assertMissing($this->_selector_pagination_btn_next);
            $this->assertEntriesDisplayed($browser, $entries_page2);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-3
     * test 4/10
     */
    public function testClickPreviousPaginationButtonToViewFirstPageOfEntries(){
        factory(Entry::class, self::ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries = $this->getApiEntries();
        $this->assertGreaterThanOrEqual(EntryController::MAX_ENTRIES_IN_RESPONSE, $entries['count']);
        unset($entries['count']);

        $this->browse(function (Browser $browser) use ($entries){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_pagination_btn_next);

            $this->assertEntriesDisplayed($browser, $entries);

            $browser
                ->click($this->_selector_pagination_btn_next)
                ->waitForLoadingToStop()
                ->click($this->_selector_pagination_btn_prev)
                ->waitForLoadingToStop();

            $this->assertEntriesDisplayed($browser, $entries);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-3
     * test 5/10
     */
    public function testPaginationWithEntryCreationUsingEntryModal(){
        factory(Entry::class, self::ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries_original = $this->getApiEntries(self::PAGE_NUMBER_ONE);
        $this->assertGreaterThan(EntryController::MAX_ENTRIES_IN_RESPONSE, $entries_original['count']);

        $this->browse(function(Browser $browser) use ($entries_original){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->click($this->_selector_pagination_btn_next)
                ->waitForLoadingToStop();

            $entries_count = $entries_original['count'];
            unset($entries_original['count']);
            $this->assertEntriesDisplayed($browser, $entries_original);

            $new_entry_date = date("Y-m-d", strtotime($entries_original[0]['entry_date'].' - 1 day'));
            $new_entry_date_to_type = $browser->processLocaleDateForTyping($browser->getDateFromLocale($browser->getBrowserLocale(), $new_entry_date));

            $browser
                ->openNewEntryModal()
                ->with($this->_selector_modal_entry, function($entry_modal_body) use ($new_entry_date_to_type){
                    $account_types = $this->getApiAccountTypes();
                    $account_type_id = collect($account_types)->pluck('id')->random(1)->first();
                    $entry_modal_body
                        ->type($this->_selector_modal_entry_field_date, $new_entry_date_to_type)
                        ->type($this->_selector_modal_entry_field_value, "342.36")
                        ->select($this->_selector_modal_entry_field_account_type, $account_type_id)
                        ->type($this->_selector_modal_entry_field_memo, "Pagination entry-modal test")
                        ->click($this->_selector_modal_entry_btn_save);
                })
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_pagination_btn_prev);

            $entries_updated = $this->getApiEntries(self::PAGE_NUMBER_ONE);
            $this->assertGreaterThan($entries_count, $entries_updated['count']);
            unset($entries_updated['count']);
            $this->assertEntriesDisplayed($browser, $entries_updated);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-3
     * test 6/10
     */
    public function testPaginationWithEntryCreationUsingTransferModal(){
        factory(Entry::class, self::ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries_original = $this->getApiEntries(self::PAGE_NUMBER_ONE);
        $this->assertGreaterThan(EntryController::MAX_ENTRIES_IN_RESPONSE, $entries_original['count']);

        $all_account_types = $this->getApiAccountTypes();
        $account_type_ids = collect($all_account_types)->pluck('id')->random(2)->toArray();

        $this->browse(function(Browser $browser) use ($entries_original, $account_type_ids){
            $entries_count = $entries_original['count'];
            unset($entries_original['count']);

            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->click($this->_selector_pagination_btn_next)
                ->waitForLoadingToStop();

            $this->assertEntriesDisplayed($browser, $entries_original);

            $new_entry_date = date("Y-m-d", strtotime($entries_original[0]['entry_date'].' - 1 day'));
            $new_entry_date_to_type = $browser->processLocaleDateForTyping($browser->getDateFromLocale($browser->getBrowserLocale(), $new_entry_date));

            $browser
                ->openTransferModal()
                ->with($this->_selector_modal_transfer, function($modal) use ($new_entry_date_to_type, $account_type_ids){
                    $modal
                        ->type($this->_selector_modal_transfer_field_date, $new_entry_date_to_type)
                        ->type($this->_selector_modal_transfer_field_value, "4820.23")
                        ->select($this->_selector_modal_transfer_field_from, $account_type_ids[0])
                        ->select($this->_selector_modal_transfer_field_to, $account_type_ids[1])
                        ->type($this->_selector_modal_transfer_field_memo, "Pagination transfer-modal test")
                        ->click($this->_selector_modal_transfer_btn_save);
                })
                ->waitForLoadingToStop()
                ->assertVisible($this->_selector_pagination_btn_prev);

            $entries_updated = $this->getApiEntries(self::PAGE_NUMBER_ONE);
            $this->assertGreaterThan($entries_count, $entries_updated['count']);
            unset($entries_updated['count']);
            $this->assertEntriesDisplayed($browser, $entries_updated);
        });
    }

    /**
     * @throws \Throwable
     *
     * @group navigation-3
     * test 7/10
     */
    public function testNextButtonNotVisibleWhenNoEntriesProvidedByFilter(){
        factory(Entry::class, self::ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());

        $this->browse(function (Browser $browser){
            $browser
                ->visit(new HomePage())
                ->waitForLoadingToStop()
                ->openFilterModal()
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_head, function(Browser $modal){
                    $filter_value = date("Y-m-d", strtotime("+10 day"));
                    $browser_date = $modal->getDateFromLocale($modal->getBrowserLocale(), $filter_value);
                    $filter_value = $modal->processLocaleDateForTyping($browser_date);
                    $modal->type($this->_selector_modal_filter_field_start_date, $filter_value);

                    $filter_value = date("Y-m-d");
                    $browser_date = $modal->getDateFromLocale($modal->getBrowserLocale(), $filter_value);
                    $filter_value = $modal->processLocaleDateForTyping($browser_date);
                    $modal->type($this->_selector_modal_filter_field_end_date, $filter_value);
                })
                ->with($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal){
                    $modal->click($this->_selector_modal_filter_btn_filter);
                })
                ->waitForLoadingToStop()
                ->with($this->_selector_table_body, function(Browser $table){
                    $table->assertMissing('tr');
                })
                ->assertMissing($this->_selector_pagination_btn_next);
        });
    }

    /**
     * @param Browser $browser
     * @param array $entries
     */
    private function assertEntriesDisplayed(Browser $browser, $entries){
        $browser->with($this->_selector_table_body, function(Browser $table) use ($entries){
            foreach($entries as $entry){
                $entry_row_selector = "#entry-".$entry['id'];
                $table
                    ->assertSeeIn($entry_row_selector.' '.$this->_selector_table_row_date, $entry['entry_date'])
                    ->assertSeeIn($entry_row_selector.' '.$this->_selector_table_row_memo, $entry['memo'])
                    ->assertSeeIn($entry_row_selector.' '.$this->_selector_table_row_value , $entry['entry_value']);
            }
        });
    }

    private function entryOverrideAttributes(){
        $account_type_id = collect($this->_account_types)->pluck('id')->random(1)->first();
        return ['disabled'=>0, 'account_type_id'=>$account_type_id];
    }

}