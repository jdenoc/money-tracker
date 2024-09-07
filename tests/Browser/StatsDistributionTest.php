<?php

namespace Tests\Browser;

use App\Models\Entry;
use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\StatsDateRange as DuskTraitStatsDateRange;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StatsPage;

/**
 * Class StatsDistributionTest
 *
 * @package Tests\Browser
 *
 * @group stats
 * @group stats-distribution
 */
class StatsDistributionTest extends StatsBase {
    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBatchFilterEntries;
    use DuskTraitStatsDateRange;
    use DuskTraitStatsSidePanel;

    // selectors
    private static $SELECTOR_STATS_DISTRIBUTION = '#stats-distribution';
    private static $SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME = '#distribution-expense-or-income';
    private static $SELECTOR_CHART_DISTRIBUTION = "canvas#pie-chart";

    // labels
    private static $LABEL_FORM_TOGGLE_EXPENSEINCOME_DEFAULT = "Expense";
    private static $LABEL_FORM_TOGGLE_EXPENSEINCOME_INCOME = "Income";

    // vue keys
    private static $VUE_KEY_STANDARDISEDATA = "standardiseData";

    // variables
    private $_color_switch_default;

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $chart_designation = 'distribution-chart';
        $this->_account_or_account_type_toggling_selector_id_label = $chart_designation;
        $this->date_range_chart_name = $chart_designation;
        $this->include_transfers_chart_name = $chart_designation;
    }

    public function setUp(): void {
        parent::setUp();
        $this->_color_switch_default = $this->tailwindColors->gray(400);
    }

    /**
     * @group stats-distribution-1
     * test 1/20
     */
    public function testSelectDistributionSidebarOption() {
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_SIDE_PANEL);
            $this->assertStatsSidePanelHeading($browser);
            $this->clickStatsSidePanelOptionDistribution($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_DISTRIBUTION);
        });
    }

    /**
     * @group stats-distribution-1
     * test 2/20
     */
    public function testFormHasCorrectElements() {
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionDistribution($browser);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_DISTRIBUTION)
                ->within(self::$SELECTOR_STATS_FORM_DISTRIBUTION, function(Browser $form) use ($accounts) {
                    // account/account-type selector
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                    // expense/income - switch
                    $this->assertToggleButtonState($form, self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME, self::$LABEL_FORM_TOGGLE_EXPENSEINCOME_DEFAULT, $this->_color_switch_default);

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
    }

    /**
     * @group stats-distribution-1
     * test 3/20
     */
    public function testDefaultDataResultsArea() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionDistribution($browser);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_RESULTS_DISTRIBUTION)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_DISTRIBUTION, self::$LABEL_NO_STATS_DATA)
                ->within(self::$SELECTOR_STATS_RESULTS_DISTRIBUTION, function(Browser $stats_results) {
                    $this->assertIncludeTransfersCheckboxButtonNotVisible($stats_results);
                });
        });
    }

    /**
     * @group stats-distribution-1
     * test 4/20
     */
    public function testGeneratingADistributionChartWontCauseSummaryTablesToBecomeVisible() {
        $this->generatingADifferentChartWontCauseSummaryTablesToBecomeVisible(
            self::$SELECTOR_STATS_SIDE_PANEL_OPTION_DISTRIBUTION,
            self::$SELECTOR_STATS_FORM_DISTRIBUTION,
            self::$SELECTOR_STATS_RESULTS_DISTRIBUTION
        );
    }

    public function providerGenerateDistributionChart(): array {
        //[$datepicker_start, $datepicker_end, $is_account_switch_toggled, $is_expense_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $include_transfers]
        return [
            // test 1/20
            'default state of account/account-types; expense; default date range' => [null, null, false, false, false, false, false],
            // test 2/20
            'default state of account/account-types; expense; default date range; include transfers' => [null, null, false, false, false, false, true],
            // test 3/20
            'default state of account/account-types; income; default date range' => [null, null, false, true, false, false, false],
            // test 4/20
            'default state of account/account-types; income; default date range; include transfers' => [null, null, false, true, false, false, true],
            // test 5/20
            'default state of account/account-types; expense; date range a year past to today' => [$this->previous_year_start, $this->today, false, false, false, false, false],
            // test 6/20
            'default state of account/account-types; expense; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, false, false, false, false, true],
            // test 7/20
            'default state of account/account-types; income; date range a year past to today' => [$this->previous_year_start, $this->today, false, true, false, false, false],
            // test 8/20
            'default state of account/account-types; income; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, false, true, false, false, true],
            // test 9/20
            'random account; expense; date range a year past to today' => [$this->previous_year_start, $this->today, false, false, true, false, false],
            // test 10/20
            'random account; expense; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, false, false, true, false, true],
            // test 11/20
            'random account; income; date range a year past to today' => [$this->previous_year_start, $this->today, false, true, true, false, false],
            // test 12/20
            'random account; income; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, false, true, true, false, true],
            // test 13/20
            'random account-type; expense; date range a year past to today' => [$this->previous_year_start, $this->today, true, false, true, false, false],
            // test 14/20
            'random account-type; expense; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, true, false, true, false, true],
            // test 15/20
            'random account-type; income; date range a year past to today' => [$this->previous_year_start, $this->today, true, true, true, false, false],
            // test 16/20
            'random account-type; income; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, true, true, true, false, true],
            // test 17/20
            'random disabled account; expense; date range a year past to today' => [$this->previous_year_start, $this->today, false, false, true, true, false],
            // test 18/20
            'random disabled account; expense; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, false, false, true, true, true],
            // test 19/20
            'random disabled account; income; date range a year past to today' => [$this->previous_year_start, $this->today, false, true, true, true, false],
            // test 20/20
            'random disabled account; income; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, false, true, true, true, true],
            // test 21/20
            'random disabled account-type; expense; date range a year past to today' => [$this->previous_year_start, $this->today, true, false, true, true, false],
            // test 22/20
            'random disabled account-type; expense; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, true, false, true, true, true],
            // test 23/20
            'random disabled account-type; income; date range a year past to today' => [$this->previous_year_start, $this->today, true, true, true, true, false],
            // test 24/20
            'random disabled account-type; income; date range a year past to today; include transfers' => [$this->previous_year_start, $this->today, true, true, true, true, true],
            // test 25/20
            'default state of account/account-types; expense; date range today only' => [$this->today, $this->today, false, false, false, false, false],
            // test 26/20
            'default state of account/account-types; expense; date range today only; include transfers' => [$this->today, $this->today, false, false, false, false, true],
        ];
    }

    /**
     * @dataProvider providerGenerateDistributionChart
     *
     * @group stats-distribution-2
     * test (see provider)/20
     */
    public function testGenerateDistributionChart(?string $datepicker_start, ?string $datepicker_end, bool $is_account_switch_toggled, bool $is_expense_switch_toggled, bool $is_random_selector_value, bool $are_disabled_select_options_available, bool $include_transfers) {
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());
        $tags = collect($this->getApiTags());

        $this->browse(function(Browser $browser) use ($datepicker_start, $datepicker_end, $is_account_switch_toggled, $is_expense_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $include_transfers, $accounts, $account_types, $tags) {
            $filter_data = [];

            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionDistribution($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_DISTRIBUTION)
                ->within(self::$SELECTOR_STATS_FORM_DISTRIBUTION, function(Browser $form) use ($is_expense_switch_toggled, $is_account_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types, &$filter_data, $datepicker_start, $datepicker_end, $tags) {
                    if ($are_disabled_select_options_available) {
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }

                    if ($is_account_switch_toggled) {
                        // switch to account-types
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : null;
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : null;
                    }

                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_account_switch_toggled, $account_or_account_type_id);

                    if ($is_expense_switch_toggled) {
                        // expense/income - switch toggled
                        $this->toggleToggleButton($form, self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME);
                        $this->assertToggleButtonState($form, self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME, self::$LABEL_FORM_TOGGLE_EXPENSEINCOME_INCOME, $this->_color_switch_default);
                    } else {
                        // expense/income - switch default
                        $this->assertToggleButtonState($form, self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME, self::$LABEL_FORM_TOGGLE_EXPENSEINCOME_DEFAULT, $this->_color_switch_default);
                    }
                    $filter_data = $this->generateFilterArrayElementExpense($filter_data, !$is_expense_switch_toggled);

                    if (is_null($datepicker_start)) {
                        $datepicker_start = $this->month_start;
                    } else {
                        $this->setDateRangeDate($form, 'start', $datepicker_start);
                    }
                    if (is_null($datepicker_end)) {
                        $datepicker_end = $this->month_end;
                    } else {
                        $this->setDateRangeDate($form, 'end', $datepicker_end);
                    }

                    $filter_data = $this->generateFilterArrayElementDatepicker($filter_data, $datepicker_start, $datepicker_end);

                    $this->generateEntryFromFilterData($filter_data, $this->getName());
                    $this->createEntryWithAllTags($is_account_switch_toggled, $account_or_account_type_id, $account_types, $tags);
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $entries = $this->getBatchedFilteredEntries($filter_data);
            $entries = $this->filterTransferEntries($entries, $include_transfers);

            $this->waitForLoadingToStop($browser);
            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_DISTRIBUTION, self::$LABEL_NO_STATS_DATA)
                ->within(self::$SELECTOR_STATS_RESULTS_DISTRIBUTION, function(Browser $stats_results_area) use ($include_transfers) {
                    $this->assertIncludeTransfersButtonDefaultState($stats_results_area);
                    if ($include_transfers) {
                        $this->clickIncludeTransfersCheckboxButton($stats_results_area);
                        $this->assertIncludesTransfersCheckboxButtonStateActive($stats_results_area);
                    }
                    $stats_results_area->assertVisible(self::$SELECTOR_CHART_DISTRIBUTION);
                })
                ->assertVue(self::$VUE_KEY_STANDARDISEDATA, $this->standardiseData($entries, $tags), self::$SELECTOR_STATS_DISTRIBUTION);
        });
    }

    /**
     * @param Collection $entries
     * @param Collection $tags
     */
    private function standardiseData($entries, $tags): array {
        $standardised_chart_data = [];

        foreach ($entries as $entry) {
            if (empty($entry['tags'])) {
                $entry['tags'][] = 0;
            }
            foreach ($entry['tags'] as $tag_id) {
                $key = $tag_id === 0 ? 'untagged' : $tags->where('id', $tag_id)->pluck('name')->first();

                if (!isset($standardised_chart_data[$key])) {
                    $standardised_chart_data[$key] = ['x' => $key,'y' => 0];
                }
                $standardised_chart_data[$key]['y'] += $entry['entry_value'];
                $standardised_chart_data[$key]['y'] = round($standardised_chart_data[$key]['y'], 2);
            }
        }
        $x_col = array_column($standardised_chart_data, 'x');
        array_multisort($x_col, SORT_ASC, $standardised_chart_data);
        return array_values($standardised_chart_data);
    }

    /**
     * Database seeder doesn't assign tags to disabled entries.
     * It's a waste of resources to do that for every test when most tests don't need that kind of data.
     * So instead for these tests, we'll create a disabled with all the tags
     *
     * @param Collection $account_types
     * @param Collection $tags
     */
    private function createEntryWithAllTags(bool $is_account_type_rather_than_account_toggled, ?int $account_or_account_type_id, $account_types, $tags): void {
        if (!empty($account_or_account_type_id)) {
            if ($is_account_type_rather_than_account_toggled) {
                $account_type_id = $account_or_account_type_id;
            } else {
                $account_type_id = $account_types->where('account_id', $account_or_account_type_id)->pluck('id')->first();
            }
        } else {
            $account_type_id = $account_types->pluck('id')->random();
        }

        $disabled_entry = Entry::factory()->create(['memo' => $this->getName().' - ALL tags', 'account_type_id' => $account_type_id, 'entry_date' => date('Y-m-d', strtotime('-1 day'))]);
        foreach ($tags->pluck('id')->all() as $tag_id) {
            $disabled_entry->tags()->attach($tag_id);
        }
    }

}
