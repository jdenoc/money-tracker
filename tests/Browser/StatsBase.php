<?php

namespace Tests\Browser;

use App\Account;
use App\AccountType;
use App\Entry;
use App\Traits\EntryFilterKeys;
use App\Traits\Tests\Dusk\BulmaDatePicker as DuskTraitBulmaDatePicker;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use App\Traits\Tests\Dusk\StatsIncludeTransfersCheckboxButton as DuskTraitStatsIncludeTransfersCheckboxButton;
use App\Traits\Tests\WithBulmaColors;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StatsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Throwable;

class StatsBase extends DuskTestCase {

    use DuskTraitBulmaDatePicker;
    use DuskTraitLoading;
    use DuskTraitStatsIncludeTransfersCheckboxButton;
    use DuskTraitStatsSidePanel;
    use EntryFilterKeys;
    use WithBulmaColors;
    use WithFaker;

    protected static $SELECTOR_STATS_FORM_SUMMARY = "#stats-form-summary";
    protected static $SELECTOR_STATS_FORM_TRENDING = '#stats-form-trending';
    protected static $SELECTOR_STATS_FORM_DISTRIBUTION = '#stats-form-distribution';
    protected static $SELECTOR_STATS_FORM_TAGS = '#stats-form-tags';

    protected static $SELECTOR_STATS_RESULTS_SUMMARY = '.stats-results-summary';
    protected static $SELECTOR_STATS_RESULTS_TRENDING = '.stats-results-trending';
    protected static $SELECTOR_STATS_RESULTS_DISTRIBUTION = '.stats-results-distribution';
    protected static $SELECTOR_STATS_RESULTS_TAGS = '.stats-results-tags';

    protected static $SELECTOR_BUTTON_GENERATE = '.generate-stats';

    protected static $LABEL_GENERATE_CHART_BUTTON = "Generate Chart";
    protected static $LABEL_NO_STATS_DATA = 'No data available';

    protected $today = '';
    protected $previous_year_start = '';
    protected $month_start = '';
    protected $month_end = '';

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->today = date("Y-m-d");
        $this->previous_year_start = date("Y-01-01", strtotime('-1 year'));
        $this->month_start = date('Y-m-01');
        $this->month_end = date("Y-m-t");
    }

    /**
     * @param string $side_panel_selector
     * @param string $stats_form_selector
     * @param string $stats_results_selector
     *
     * @throws Throwable
     */
    protected function generatingADifferentChartWontCauseSummaryTablesToBecomeVisible($side_panel_selector, $stats_form_selector, $stats_results_selector){
        // TODO: rewrite this to accept any stats component; not just redirect to the stats-summary component
        // TODO: wait until all other stats components have been adjusted
        $this->browse(function (Browser $browser) use ($side_panel_selector, $stats_form_selector, $stats_results_selector){
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY);

            $this->clickStatsSidePanelOption($browser, $side_panel_selector);
            $browser
                ->assertVisible($stats_form_selector)
                ->with($stats_form_selector, function(Browser $form){
                    $this->setDateRange($form, $this->previous_year_start, $this->today);
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });
            $this->waitForLoadingToStop($browser);
            $browser->assertDontSeeIn($stats_results_selector, self::$LABEL_NO_STATS_DATA);
            $this->assertIncludeTransfersCheckboxButtonDefaultState($browser);

            $this->clickStatsSidePanelOptionSummary($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_SUMMARY, self::$LABEL_NO_STATS_DATA);
            $this->assertIncludeTransfersCheckboxButtonNotVisible($browser);
        });
    }

    /**
     * Sometimes we select a collection of filter parameters that result in no entries being available.
     * In those situations, we need to make sure that at least one entry does exist.
     *
     * @param array $filter_data
     * @param string $memo
     */
    protected function generateEntryFromFilterData(array $filter_data, string $memo = ''){
        $new_entry_data = ['disabled'=>false];
        if(!empty($memo)){
            $new_entry_data['memo'] = $memo;
        }
        
        $new_entry_data['entry_date'] = $this->faker
            ->dateTimeBetween($filter_data[self::$FILTER_KEY_START_DATE], $filter_data[self::$FILTER_KEY_END_DATE])
            ->format("Y-m-d");
        if(isset($filter_data[self::$FILTER_KEY_EXPENSE])){
            $new_entry_data['expense'] = $filter_data[self::$FILTER_KEY_EXPENSE];
        }

        if(!empty($filter_data[self::$FILTER_KEY_ACCOUNT_TYPE])){
            $new_entry_data['account_type_id'] = $filter_data[self::$FILTER_KEY_ACCOUNT_TYPE];
        } elseif(!empty($filter_data[self::$FILTER_KEY_ACCOUNT])){
            $account = Account::find_account_with_types($filter_data[self::$FILTER_KEY_ACCOUNT]);
            $new_entry_data['account_type_id'] = $account->account_types->first()->id;
        } else {
            // Can't leave the assignment up to RNG in the factory.
            // Could result in assigning an ID that doesn't exist
            $new_entry_data['account_type_id'] = AccountType::all()->pluck('id')->random();
        }

        $entry = factory(Entry::class)->create($new_entry_data);
        if(!empty($filter_data[self::$FILTER_KEY_TAGS])){
            $entry->tags()->attach($filter_data[self::$FILTER_KEY_TAGS]);
        }
    }

    /**
     * @param Collection $entries
     * @param bool $is_transfer
     * @return Collection
     */
    protected function filterTransferEntries($entries, bool $is_transfer){
        // TODO: take into account external transfers (e.g.: transfer_entry_id=0)
        if(!$is_transfer){
            return $entries->where('is_transfer', false);
        } else {
            return $entries;
        }
    }

}