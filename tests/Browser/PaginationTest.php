<?php

namespace Tests\Browser;

use App\Models\Entry;
use App\Traits\MaxEntryResponseValue;
use App\Traits\Tests\Dusk\BrowserDateUtil as DuskTraitBrowserDateUtil;
use App\Traits\Tests\Dusk\BrowserVisibilityUtil as DustTraitBrowserVisibilityUtil;
use App\Traits\Tests\Dusk\EntryModal as DuskTraitEntryModal;
use App\Traits\Tests\Dusk\FilterModal as DuskTraitFilterModal;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\Navbar as DuskTraitNavbar;
use Illuminate\Support\Facades\DB;
use Tests\Browser\Pages\HomePage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Traits\HomePageSelectors;
use Throwable;

/**
 * Class PaginationTest
 *
 * @package Tests\Browser
 *
 * @group navigation
 * @group home
 */
class PaginationTest extends DuskTestCase {
    use DuskTraitBrowserDateUtil;
    use DustTraitBrowserVisibilityUtil;
    use DuskTraitEntryModal;
    use DuskTraitFilterModal;
    use DuskTraitLoading;
    use DuskTraitNavbar;
    use HomePageSelectors;
    use MaxEntryResponseValue;

    // pages
    const PAGE_NUMBER_ZERO = 0;
    const PAGE_NUMBER_ONE = 1;
    const PAGE_NUMBER_TWO = 2;

    // entry counts
    private static int $ENTRY_COUNT_ONE = 10;   // number of entries needed to be generated for 1 page of results
    private static int $ENTRY_COUNT_TWO;        // number of entries needed to be generated for 2 pages of results; set in constructor
    private static int $ENTRY_COUNT_THREE;      // number of entries needed to be generated for 3 pages of results; set in constructor

    // variables
    private $_account_types = [];

    public function __construct($name = null) {
        parent::__construct($name);

        self::$ENTRY_COUNT_TWO = (self::$MAX_ENTRIES_IN_RESPONSE * 2) - 10;
        self::$ENTRY_COUNT_THREE = (self::$MAX_ENTRIES_IN_RESPONSE * 3) - 25;
    }

    public function setUp(): void {
        parent::setUp();
        // clear the `entries` table, so we can test against an exact amount of entries.
        DB::table('entries')->truncate();
        $this->_account_types = $this->getApiAccountTypes();
    }

    /**
     * @throws Throwable
     *
     * @group navigation-4
     * test 1/20
     */
    public function testPaginationButtonsNotVisibleIfEntryCountLessThanPageMax() {
        Entry::factory()->count(self::$ENTRY_COUNT_ONE)->create($this->entryOverrideAttributes());
        $entries = $this->getApiEntries();
        $this->assertLessThan(self::$MAX_ENTRIES_IN_RESPONSE, $entries['count']);

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_pagination_btn_next)
                ->assertMissing($this->_selector_pagination_btn_prev);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-4
     * test 2/20
     */
    public function testNextPaginationButtonVisibleIfEntryCountGreaterThanPageMax() {
        Entry::factory()->count(self::$ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries = $this->getApiEntries();
        $this->assertGreaterThanOrEqual(self::$MAX_ENTRIES_IN_RESPONSE, $entries['count']);

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $browser
                ->assertMissing($this->_selector_pagination_btn_prev)
                ->assertVisible($this->_selector_pagination_btn_next);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-4
     * test 3/20
     */
    public function testPreviousPaginationButtonsVisiblePagedToNextPage() {
        Entry::factory()->count(self::$ENTRY_COUNT_THREE)->create($this->entryOverrideAttributes());
        $entries_page1 = $this->getApiEntries(self::PAGE_NUMBER_ONE);
        $entries_page2 = $this->getApiEntries(self::PAGE_NUMBER_TWO);

        $this->assertEquals($entries_page1['count'], $entries_page2['count']);
        $this->assertGreaterThanOrEqual(self::$MAX_ENTRIES_IN_RESPONSE * 2, $entries_page1['count']);
        $this->assertLessThan(self::$MAX_ENTRIES_IN_RESPONSE * 3, $entries_page2['count']);
        unset($entries_page1['count'], $entries_page2['count']);

        $this->browse(function(Browser $browser) use ($entries_page1, $entries_page2) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->clickNextButton($browser);

            $this->assertEntriesDisplayed($browser, $entries_page1);

            $browser->assertVisible($this->_selector_pagination_btn_prev);

            $this->clickNextButton($browser);
            $browser
                ->assertVisible($this->_selector_pagination_btn_prev)
                ->assertMissing($this->_selector_pagination_btn_next);
            $this->assertEntriesDisplayed($browser, $entries_page2);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-4
     * test 4/20
     */
    public function testClickPreviousPaginationButtonToViewFirstPageOfEntries() {
        Entry::factory()->count(self::$ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries = $this->getApiEntries();
        $this->assertGreaterThanOrEqual(self::$MAX_ENTRIES_IN_RESPONSE, $entries['count']);
        unset($entries['count']);

        $this->browse(function(Browser $browser) use ($entries) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);

            $this->assertEntriesDisplayed($browser, $entries);

            $this->clickNextButton($browser);
            $this->clickPrevButton($browser);

            $this->assertEntriesDisplayed($browser, $entries);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-4
     * test 5/20
     */
    public function testPaginationWithEntryCreationUsingEntryModal() {
        Entry::factory()->count(self::$ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries_original = $this->getApiEntries(self::PAGE_NUMBER_ONE);
        $this->assertGreaterThan(self::$MAX_ENTRIES_IN_RESPONSE, $entries_original['count']);

        $this->browse(function(Browser $browser) use ($entries_original) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->clickNextButton($browser);

            $entries_count = $entries_original['count'];
            unset($entries_original['count']);
            $this->assertEntriesDisplayed($browser, $entries_original);

            $new_entry_date = date("Y-m-d", strtotime($entries_original[0]['entry_date'].' - 1 day'));
            $new_entry_date_to_type = $this->processLocaleDateForTyping($this->getDateFromLocale($this->getBrowserLocale($browser), $new_entry_date));

            $this->openNewEntryModal($browser);
            $browser
                ->within($this->_selector_modal_entry, function(Browser $entry_modal_body) use ($new_entry_date_to_type) {
                    $account_types = $this->getApiAccountTypes();
                    $account_type_id = collect($account_types)->pluck('id')->random(1)->first();
                    $entry_modal_body
                        ->type($this->_selector_modal_entry_field_date, $new_entry_date_to_type)
                        ->type($this->_selector_modal_entry_field_value, "342.36")
                        ->select($this->_selector_modal_entry_field_account_type, $account_type_id)
                        ->type($this->_selector_modal_entry_field_memo, "Pagination entry-modal test")
                        ->click($this->_selector_modal_entry_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser->assertVisible($this->_selector_pagination_btn_prev);

            $entries_updated = $this->getApiEntries(self::PAGE_NUMBER_ONE);
            $this->assertGreaterThan($entries_count, $entries_updated['count']);
            unset($entries_updated['count']);
            $this->assertEntriesDisplayed($browser, $entries_updated);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-4
     * test 6/20
     */
    public function testPaginationWithEntryCreationUsingTransferModal() {
        Entry::factory()->count(self::$ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());
        $entries_original = $this->getApiEntries(self::PAGE_NUMBER_ONE);
        $this->assertGreaterThan(self::$MAX_ENTRIES_IN_RESPONSE, $entries_original['count']);

        $all_account_types = $this->getApiAccountTypes();
        $account_type_ids = collect($all_account_types)->pluck('id')->random(2)->toArray();

        $this->browse(function(Browser $browser) use ($entries_original, $account_type_ids) {
            $entries_count = $entries_original['count'];
            unset($entries_original['count']);

            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->clickNextButton($browser);

            $this->assertEntriesDisplayed($browser, $entries_original);

            $new_entry_date = date("Y-m-d", strtotime($entries_original[0]['entry_date'].' - 1 day'));
            $new_entry_date_to_type = $this->processLocaleDateForTyping($this->getDateFromLocale($this->getBrowserLocale($browser), $new_entry_date));

            $this->openTransferModal($browser);
            $browser
                ->within($this->_selector_modal_transfer, function(Browser $modal) use ($new_entry_date_to_type, $account_type_ids) {
                    $modal
                        ->type($this->_selector_modal_transfer_field_date, $new_entry_date_to_type)
                        ->type($this->_selector_modal_transfer_field_value, "4820.23")
                        ->select($this->_selector_modal_transfer_field_from, $account_type_ids[0])
                        ->select($this->_selector_modal_transfer_field_to, $account_type_ids[1])
                        ->type($this->_selector_modal_transfer_field_memo, "Pagination transfer-modal test")
                        ->click($this->_selector_modal_transfer_btn_save);
                });
            $this->waitForLoadingToStop($browser);
            $browser->assertVisible($this->_selector_pagination_btn_prev);

            $entries_updated = $this->getApiEntries(self::PAGE_NUMBER_ONE);
            $this->assertGreaterThan($entries_count, $entries_updated['count']);
            unset($entries_updated['count']);
            $this->assertEntriesDisplayed($browser, $entries_updated);
        });
    }

    /**
     * @throws Throwable
     *
     * @group navigation-4
     * test 7/20
     */
    public function testNextButtonNotVisibleWhenNoEntriesProvidedByFilter() {
        Entry::factory()->count(self::$ENTRY_COUNT_TWO)->create($this->entryOverrideAttributes());

        $this->browse(function(Browser $browser) {
            $browser->visit(new HomePage());
            $this->waitForLoadingToStop($browser);
            $this->openFilterModal($browser);
            $browser
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_head, function(Browser $modal) {
                    $filter_value = date("Y-m-d", strtotime("+10 day"));
                    $browser_date = $this->getDateFromLocale($this->getBrowserLocale($modal), $filter_value);
                    $filter_value = $this->processLocaleDateForTyping($browser_date);
                    $modal->type($this->_selector_modal_filter_field_start_date, $filter_value);

                    $filter_value = date("Y-m-d");
                    $browser_date = $this->getDateFromLocale($this->getBrowserLocale($modal), $filter_value);
                    $filter_value = $this->processLocaleDateForTyping($browser_date);
                    $modal->type($this->_selector_modal_filter_field_end_date, $filter_value);
                })
                ->within($this->_selector_modal_filter.' '.$this->_selector_modal_foot, function(Browser $modal) {
                    $modal->click($this->_selector_modal_filter_btn_filter);
                });
            $this->waitForLoadingToStop($browser);
            $browser
                ->within($this->_selector_table_body, function(Browser $table) {
                    $table->assertMissing('tr');
                })
                ->assertMissing($this->_selector_pagination_btn_next);
        });
    }

    private function assertEntriesDisplayed(Browser $browser, array $entries): void {
        $browser->within($this->_selector_table_body, function(Browser $table) use ($entries) {
            foreach ($entries as $entry) {
                $entry_row_selector = sprintf(self::$PLACEHOLDER_SELECTOR_EXISTING_ENTRY_ROW, $entry['id']);
                $table
                    ->assertSeeIn($entry_row_selector.' '.$this->_selector_table_row_date, $entry['entry_date'])
                    ->assertSeeIn($entry_row_selector.' '.$this->_selector_table_row_memo, $entry['memo'])
                    ->assertSeeIn($entry_row_selector.' '.$this->_selector_table_row_value, $entry['entry_value']);
            }
        });
    }

    private function entryOverrideAttributes(): array {
        $account_type_id = collect($this->_account_types)->pluck('id')->random(1)->first();
        return ['account_type_id' => $account_type_id];
    }

    private function clickNextButton(Browser $browser): void {
        $browser
            ->scrollIntoView($this->_selector_pagination_btn_next)
            ->assertVisible($this->_selector_pagination_btn_next)
            ->click($this->_selector_pagination_btn_next);
        $this->waitForLoadingToStop($browser);
        // should scroll to top of page
        $this->isVisibleInViewport($browser, $this->_selector_table.' '.$this->_selector_table_head);
    }

    private function clickPrevButton(Browser $browser): void {
        $browser
            ->scrollIntoView($this->_selector_pagination_btn_prev)
            ->assertVisible($this->_selector_pagination_btn_prev)
            ->click($this->_selector_pagination_btn_prev);
        $this->waitForLoadingToStop($browser);
        // should scroll to/remain at the bottom of the page
        $this->isVisibleInViewport($browser, $this->_selector_pagination_btn_next);
    }

}
