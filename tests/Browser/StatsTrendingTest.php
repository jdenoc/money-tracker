<?php

namespace Tests\Browser;

use App\Models\Currency;
use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\StatsDateRange as DuskTraitStatsDateRange;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use Brick\Money\Money;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StatsPage;

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

    // selectors
    private const SELECTOR_STATS_TRENDING = "#stats-trending";
    private const SELECTOR_CHART_TRENDING = 'canvas#line-chart';

    // vue keys
    private const VUE_KEY_EXPENSEDATA = "expenseData";
    private const VUE_KEY_INCOMEDATA = "incomeData";
    private const VUE_KEY_COMPARISONDATA = 'comparisonData';
    private const VUE_KEY_PERIODTOTALSDATA = 'periodTotalsData';

    public function __construct($name = null) {
        parent::__construct($name);
        $chart_designation = 'trending-chart';
        $this->_account_or_account_type_toggling_selector_id_label = $chart_designation;
        $this->date_range_chart_name = $chart_designation;
        $this->include_transfers_chart_name = $chart_designation;
    }

    /**
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
     * @group stats-trending-1
     * test 2/20
     */
    public function testFormHasCorrectElements() {
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTrending($browser);

            $browser
                ->assertVisible(self::SELECTOR_STATS_TRENDING)
                ->within(self::SELECTOR_STATS_TRENDING, function(Browser $stats_trending) use ($accounts) {
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
     * @group stats-trending-1
     * test 3/20
     */
    public function testDefaultDataResultsArea() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTrending($browser);
            $browser
                ->assertVisible(self::SELECTOR_STATS_TRENDING)
                ->within(self::SELECTOR_STATS_TRENDING, function(Browser $stats_trending) {
                    $stats_trending
                        ->assertVisible(self::$SELECTOR_STATS_RESULTS_TRENDING)
                        ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_TRENDING, self::$LABEL_NO_STATS_DATA);
                    $this->assertIncludeTransfersCheckboxButtonNotVisible($stats_trending);
                });
        });
    }

    public static function providerTestGenerateTrendingChart(): array {
        return [
            //[$datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $include_transfers]
            // test 4/20
            'defaults account/account-type & date-picker values' => [null, null, false, false, false, false],
            // test 5/20
            'defaults account/account-type & date-picker values & include transfers checkbox button clicked' => [null, null, false, false, false, true],
            // test 6/20
            'date-picker previous year start to present & default account/account-type' => [self::getDatePreviousYearStart(), self::getDateToday(), false, false, false, false],
            // test 7/20
            'date-picker previous year start to present & default account/account-type & include transfers checkbox button clicked' => [self::getDatePreviousYearStart(), self::getDateToday(), false, false, false, true],
            // test 8/20
            'date-picker previous year start to present & random account' => [self::getDatePreviousYearStart(), self::getDateToday(), false, true, false, false],
            // test 9/20
            'date-picker previous year start to present & random account & include transfers checkbox button clicked' => [self::getDatePreviousYearStart(), self::getDateToday(), false, true, false, true],
            // test 10/20
            'date-picker previous year start to present & random account-type' => [self::getDatePreviousYearStart(), self::getDateToday(), true, true, false, false],
            // test 11/20
            'date-picker previous year start to present & random account-type & include transfers checkbox button clicked' => [self::getDatePreviousYearStart(), self::getDateToday(), true, true, false, true],
            // test 12/20
            'date-picker previous year start to present & random disabled account' => [self::getDatePreviousYearStart(), self::getDateToday(), false, true, false, false],
            // test 13/20
            'date-picker previous year start to present & random disabled account & include transfers checkbox button clicked' => [self::getDatePreviousYearStart(), self::getDateToday(), false, true, false, true],
            // test 14/20
            'date-picker previous year start to present & random disabled account-type' => [self::getDatePreviousYearStart(), self::getDateToday(), true, true, false, false],
            // test 15/20
            'date-picker previous year start to present & random disabled account-type & include transfers checkbox button clicked' => [self::getDatePreviousYearStart(), self::getDateToday(), true, true, false, true],
            // test 16/20
            'defaults account/account-type; date-picker today ONLY' => [self::getDateToday(), self::getDateToday(), false, false, false, false],
            // test 17/20
            'defaults account/account-type; date-picker today ONLY; include transfers' => [self::getDateToday(), self::getDateToday(), false, false, false, true],
        ];
    }

    /**
     * @dataProvider providerTestGenerateTrendingChart
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
                        $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : null;
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : null;
                    }

                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_switch_toggled, $account_or_account_type_id);

                    if (is_null($datepicker_start)) {
                        $datepicker_start = self::getDateMonthStart();
                    } else {
                        $this->setDateRangeDate($form, 'start', $datepicker_start);
                    }
                    if (is_null($datepicker_end)) {
                        $datepicker_end = self::getDateMonthEnd();
                    } else {
                        $this->setDateRangeDate($form, 'end', $datepicker_end);
                    }

                    $filter_data = $this->generateFilterArrayElementDatepicker($filter_data, $datepicker_start, $datepicker_end);

                    $this->generateEntryFromFilterData($filter_data, $this->name());
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $this->waitForLoadingToStop($browser);
            $entries = $this->getBatchedFilteredEntries($filter_data);
            $entries = $this->filterTransferEntries($entries, $include_transfers);

            $income_data = $this->standardiseChartData($entries, false);
            $expense_data = $this->standardiseChartData($entries, true);
            $comparison_data = $this->comparisonChartData($income_data, $expense_data);
            $period_totals_data = $this->periodTotalsChartData($comparison_data);

            $income_data = $this->convertMoneyIntoFloat($income_data);
            $expense_data = $this->convertMoneyIntoFloat($expense_data);
            $comparison_data = $this->convertMoneyIntoFloat($comparison_data);
            $period_totals_data = $this->convertMoneyIntoFloat($period_totals_data);

            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_TRENDING, self::$LABEL_NO_STATS_DATA)
                ->within(self::$SELECTOR_STATS_RESULTS_TRENDING, function(Browser $stats_results) use ($include_transfers) {
                    $this->assertIncludeTransfersButtonDefaultState($stats_results);
                    if ($include_transfers) {
                        $this->clickIncludeTransfersCheckboxButton($stats_results);
                        $this->assertIncludesTransfersCheckboxButtonStateActive($stats_results);
                    }

                    //  line-chart graph canvas should be visible
                    $stats_results->assertVisible(self::SELECTOR_CHART_TRENDING);
                })
                ->assertVue(self::VUE_KEY_INCOMEDATA, $income_data, self::SELECTOR_STATS_TRENDING)
                ->assertVue(self::VUE_KEY_EXPENSEDATA, $expense_data, self::SELECTOR_STATS_TRENDING)
                ->assertVue(self::VUE_KEY_COMPARISONDATA, $comparison_data, self::SELECTOR_STATS_TRENDING)
                ->assertVue(self::VUE_KEY_PERIODTOTALSDATA, $period_totals_data, self::SELECTOR_STATS_TRENDING);
        });
    }

    /**
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
     */
    private function standardiseChartData(Collection $entries, bool $is_expense): array {
        $standardised_chart_data = [];
        $filtered_entries = $entries->where('expense', $is_expense);
        foreach ($filtered_entries as $entry) {
            // condense data points with similar entry_date values
            $key = $entry['entry_date'];
            if (!isset($standardised_chart_data[$key])) {
                $standardised_chart_data[$key] = ['x' => $key, 'y' => Money::zero(Currency::DEFAULT_CURRENCY_CODE)];
            }
            $standardised_chart_data[$key]['y'] = $standardised_chart_data[$key]['y']->plus($entry['entry_value']);
        }

        ksort($standardised_chart_data);
        return array_values($standardised_chart_data);
    }

    /**
     * Code in the method is translated from JavaScript located here:
     *  resources/js/components/stats/trending-chart.vue => computed.comparisonData()
     */
    private function comparisonChartData(array $income_data, array $expense_data): array {
        $comparison_data = [];
        foreach ($income_data as $datum) {
            $key = $datum['x'];
            if (!isset($comparison_data[$key])) {
                $comparison_data[$key] = ['x' => $key, 'y' => Money::zero(Currency::DEFAULT_CURRENCY_CODE)];
            }
            $comparison_data[$key]['y'] = $comparison_data[$key]['y']->plus($datum['y']);
        }
        foreach ($expense_data as $datum) {
            $key = $datum['x'];
            if (!isset($comparison_data[$key])) {
                $comparison_data[$key] = ['x' => $key, 'y' => Money::zero(Currency::DEFAULT_CURRENCY_CODE)];
            }
            $comparison_data[$key]['y'] = $comparison_data[$key]['y']->minus($datum['y']);
        }
        ksort($comparison_data);
        return array_values($comparison_data);
    }

    /**
     * Code in the method is translated from JavaScript located here:
     *  resources/js/components/stats/trending-chart.vue => computed.periodTotalData
     */
    private function periodTotalsChartData(array $comparison_chart_data): array {
        $period_totals_data = [];
        $previous_value = Money::zero(Currency::DEFAULT_CURRENCY_CODE);
        foreach ($comparison_chart_data as $i => $chart_datum) {
            $new_total = $previous_value->plus($chart_datum['y']);
            $period_totals_data[$i] = ['x' => $chart_datum['x'], 'y' => $new_total];
            $previous_value = $new_total;
        }
        return $period_totals_data;
    }

    private function convertMoneyIntoFloat(array $chartData): array {
        return array_map(
            function($datum) {
                $datum['y'] = $datum['y']->getAmount()->toFloat();
                return $datum;
            },
            $chartData
        );
    }

}
