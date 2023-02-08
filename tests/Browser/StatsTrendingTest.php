<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\StatsDateRange as DuskTraitStatsDateRange;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StatsPage;
use Throwable;

/**
 * Class StatsTrendingTest
 *
 * @package Tests\Browser
 *
 * @group stats
 * @group stats-trending
 */
class StatsTrendingTest extends StatsBase {
    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBatchFilterEntries;
    use DuskTraitStatsDateRange;
    use DuskTraitStatsSidePanel;

    private static $SELECTOR_STATS_TRENDING = "#stats-trending";
    private static $SELECTOR_CHART_TRENDING = 'canvas#line-chart';

    private static $VUE_KEY_EXPENSEDATA = "expenseData";
    private static $VUE_KEY_INCOMEDATA = "incomeData";
    private static $VUE_KEY_COMPARISONDATA = 'comparisonData';
    private static $VUE_KEY_PERIODTOTALSDATA = 'periodTotalsData';

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $chart_designation = 'trending-chart';
        $this->_account_or_account_type_toggling_selector_id_label = $chart_designation;
        $this->date_range_chart_name = $chart_designation;
        $this->include_transfers_chart_name = $chart_designation;
    }

    /**
     * @throws Throwable
     *
     * @group stats-trending-1
     * test 1/20
     */
    public function testSelectTrendingSidebarOption() {
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_SIDE_PANEL);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY);
            $this->clickStatsSidePanelOptionTrending($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_TRENDING);
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-trending-1
     * test 2/20
     */
    public function testFormHasCorrectElements() {
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTrending($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_TRENDING)
                ->within(self::$SELECTOR_STATS_TRENDING, function(Browser $stats_trending) use ($accounts) {
                    $stats_trending
                        ->assertVisible(self::$SELECTOR_STATS_FORM_TRENDING)
                        ->within(self::$SELECTOR_STATS_FORM_TRENDING, function(Browser $form) use ($accounts) {
                            // account/account-type selector
                            $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                            // date range
                            $this->assertDefaultStateDateRange($form);

                            // button
                            $form
                                ->assertVisible(self::$SELECTOR_BUTTON_GENERATE)
                                ->assertSeeIn(self::$SELECTOR_BUTTON_GENERATE, self::$LABEL_GENERATE_CHART_BUTTON);
                            $this->assertElementBackgroundColor($form, self::$SELECTOR_BUTTON_GENERATE, $this->tailwindColors->blue(600));
                            $this->assertElementTextColor($form, self::$SELECTOR_BUTTON_GENERATE, $this->tailwindColors->white());
                        });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-trending-1
     * test 3/20
     */
    public function testDefaultDataResultsArea() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTrending($browser);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_TRENDING)
                ->within(self::$SELECTOR_STATS_TRENDING, function(Browser $stats_trending) {
                    $stats_trending
                        ->assertVisible(self::$SELECTOR_STATS_RESULTS_TRENDING)
                        ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_TRENDING, self::$LABEL_NO_STATS_DATA);
                    $this->assertIncludeTransfersCheckboxButtonNotVisible($stats_trending);
                });
        });
    }

    public function providerTestGenerateTrendingChart(): array {
        return [
            //[$datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $include_transfers]
            // defaults account/account-type & date-picker values
            [null, null, false, false, false, false],  // test 4/20
            // defaults account/account-type & date-picker values & include transfers checkbox button clicked
            [null, null, false, false, false, true],  // test 5/20
            // date-picker previous year start to present & default account/account-type
            [$this->previous_year_start, $this->today, false, false, false, false],    // test 6/20
            // date-picker previous year start to present & default account/account-type & include transfers checkbox button clicked
            [$this->previous_year_start, $this->today, false, false, false, true],    // test 7/20
            // date-picker previous year start to present & random account
            [$this->previous_year_start, $this->today, false, true, false, false],     // test 8/20
            // date-picker previous year start to present & random account & include transfers checkbox button clicked
            [$this->previous_year_start, $this->today, false, true, false, true],     // test 9/20
            // date-picker previous year start to present & random account-type
            [$this->previous_year_start, $this->today, true, true, false, false],      // test 10/20
            // date-picker previous year start to present & random account-type & include transfers checkbox button clicked
            [$this->previous_year_start, $this->today, true, true, false, true],      // test 11/20
            // date-picker previous year start to present & random disabled account
            [$this->previous_year_start, $this->today, false, true, false, false],     // test 12/20
            // date-picker previous year start to present & random disabled account & include transfers checkbox button clicked
            [$this->previous_year_start, $this->today, false, true, false, true],     // test 13/20
            // date-picker previous year start to present & random disabled account-type
            [$this->previous_year_start, $this->today, true, true, false, false],      // test 14/20
            // date-picker previous year start to present & random disabled account-type & include transfers checkbox button clicked
            [$this->previous_year_start, $this->today, true, true, false, true],      // test 15/20
            // defaults account/account-type; date-picker today ONLY
            [$this->today, $this->today, false, false, false, false],  // test 16/20
            // defaults account/account-type; date-picker today ONLY; include transfers
            [$this->today, $this->today, false, false, false, true],  // test 17/20
        ];
    }

    /**
     * @dataProvider providerTestGenerateTrendingChart
     *
     * @param string|null $datepicker_start
     * @param string|null $datepicker_end
     * @param bool $is_switch_toggled
     * @param bool $is_random_selector_value
     * @param bool $are_disabled_select_options_available
     * @param bool $include_transfers
     *
     * @throws Throwable
     *
     * @group stats-trending-1
     * test (see provider)/20
     */
    public function testGenerateTrendingChart(?string $datepicker_start, ?string $datepicker_end, bool $is_switch_toggled, bool $is_random_selector_value, bool $are_disabled_select_options_available, bool $include_transfers) {
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());

        $this->browse(function(Browser $browser) use ($accounts, $account_types, $datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $include_transfers) {
            $filter_data = [];

            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTrending($browser);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_TRENDING)
                ->within(self::$SELECTOR_STATS_FORM_TRENDING, function(Browser $form) use ($accounts, $account_types, $datepicker_start, $datepicker_end, $is_switch_toggled, &$filter_data, $is_random_selector_value, $are_disabled_select_options_available) {
                    if ($are_disabled_select_options_available) {
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }
                    if ($is_switch_toggled) {
                        // switch to account-types
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : '';
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : '';
                    }

                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_switch_toggled, $account_or_account_type_id);

                    if (!is_null($datepicker_start)) {
                        $this->setDateRangeDate($form, 'start', $datepicker_start);
                    } else {
                        $datepicker_start = $this->month_start;
                    }
                    if (!is_null($datepicker_end)) {
                        $this->setDateRangeDate($form, 'end', $datepicker_end);
                    } else {
                        $datepicker_end = $this->month_end;
                    }

                    $filter_data = $this->generateFilterArrayElementDatepicker($filter_data, $datepicker_start, $datepicker_end);

                    $this->generateEntryFromFilterData($filter_data, $this->getName());
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $this->waitForLoadingToStop($browser);
            $entries = $this->getBatchedFilteredEntries($filter_data);
            $entries = $this->filterTransferEntries($entries, $include_transfers);

            $income_data = $this->standardiseChartData($entries, false);
            $expense_data = $this->standardiseChartData($entries, true);
            $comparison_data = $this->comparisonChartData($income_data, $expense_data);
            $period_totals_data = $this->periodTotalsChartData($comparison_data);

            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_TRENDING, self::$LABEL_NO_STATS_DATA)
                ->within(self::$SELECTOR_STATS_RESULTS_TRENDING, function(Browser $stats_results) use ($include_transfers) {
                    $this->assertIncludeTransfersButtonDefaultState($stats_results);
                    if ($include_transfers) {
                        $this->clickIncludeTransfersCheckboxButton($stats_results);
                        $this->assertIncludesTransfersCheckboxButtonStateActive($stats_results);
                    }

                    //  line-chart graph canvas should be visible
                    $stats_results->assertVisible(self::$SELECTOR_CHART_TRENDING);
                })
                ->assertVue(self::$VUE_KEY_INCOMEDATA, $income_data, self::$SELECTOR_STATS_TRENDING)
                ->assertVue(self::$VUE_KEY_EXPENSEDATA, $expense_data, self::$SELECTOR_STATS_TRENDING)
                ->assertVue(self::$VUE_KEY_COMPARISONDATA, $comparison_data, self::$SELECTOR_STATS_TRENDING)
                ->assertVue(self::$VUE_KEY_PERIODTOTALSDATA, $period_totals_data, self::$SELECTOR_STATS_TRENDING);
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-trending-1
     * test 18/20
     */
    public function testGeneratingATrendingChartWontCauseSummaryTablesToBecomeVisible() {
        $this->generatingADifferentChartWontCauseSummaryTablesToBecomeVisible(
            self::$SELECTOR_STATS_SIDE_PANEL_OPTION_TRENDING,
            self::$SELECTOR_STATS_FORM_TRENDING,
            self::$SELECTOR_STATS_RESULTS_TRENDING
        );
    }

    /**
     * Code in the method is translated from JavaScript located here:
     *  resources/js/components/stats/trending-chart.vue => methods.standardiseData()
     *
     * @param Collection $entries
     * @param bool $is_expense
     * @return array
     *
     */
    private function standardiseChartData(Collection $entries, bool $is_expense): array {
        $standardised_chart_data = [];
        $filtered_entries = $entries->where('expense', $is_expense);
        foreach ($filtered_entries as $entry) {
            // condense data points with similar entry_date values
            $key = $entry['entry_date'];
            if (!isset($standardised_chart_data[$key])) {
                $standardised_chart_data[$key] = ['x'=>$key, 'y'=>0];
            }
            $standardised_chart_data[$key]['y'] += $entry['entry_value'];
        }

        ksort($standardised_chart_data);
        return array_values($standardised_chart_data);
    }

    /**
     * Code in the method is translated from JavaScript located here:
     *  resources/js/components/stats/trending-chart.vue => computed.comparisonData()
     *
     * @param array $income_data
     * @param array $expense_data
     * @return array
     */
    private function comparisonChartData(array $income_data, array $expense_data): array {
        $comparison_data = [];
        foreach ($income_data as $datum) {
            $key = $datum['x'];
            if (!isset($comparison_data[$key])) {
                $comparison_data[$key] = ['x'=>$key, 'y'=>0];
            }
            $comparison_data[$key]['y'] += $datum['y'];
        }
        foreach ($expense_data as $datum) {
            $key = $datum['x'];
            if (!isset($comparison_data[$key])) {
                $comparison_data[$key] = ['x'=>$key, 'y'=>0];
            }
            $comparison_data[$key]['y'] -= $datum['y'];
        }
        ksort($comparison_data);
        return array_values($comparison_data);
    }

    /**
     * Code in the method is translated from JavaScript located here:
     *  resources/js/components/stats/trending-chart.vue => computed.periodTotalData
     *
     * @param array $comparison_chart_data
     * @return array
     */
    private function periodTotalsChartData(array $comparison_chart_data): array {
        $period_totals_data = [];
        $previous_value = 0;
        foreach ($comparison_chart_data as $i=>$chart_datum) {
            $new_total = $previous_value+$chart_datum['y'];
            $period_totals_data[$i] = ['x'=>$chart_datum['x'], 'y'=>$new_total];
            $previous_value = $new_total;
        }
        return $period_totals_data;
    }

}
