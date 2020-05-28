<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\BulmaDatePicker as DuskTraitBulmaDatePicker;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use App\Traits\Tests\InjectDatabaseStateIntoException;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StatsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Throwable;

/**
 * Class StatsDistributionTest
 *
 * @package Tests\Browser
 *
 * @group stats
 * @group stats-distribution
 */
class StatsDistributionTest extends DuskTestCase {

    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBulmaDatePicker;
    use DuskTraitBatchFilterEntries;
    use DuskTraitLoading;
    use DuskTraitStatsSidePanel;

    use InjectDatabaseStateIntoException;

    private static $SELECTOR_STATS_DISTRIBUTION = '#stats-distribution';
    private static $SELECTOR_STATS_FORM_DISTRIBUTION = '#stats-form-distribution';
    private static $SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME = '#distribution-expense-or-income';
    private static $SELECTOR_STATS_FORM_BUTTON_GENERATE = '.generate-stats';
    private static $SELECTOR_STATS_RESULTS_AREA = ".stats-results-distribution";
    private static $SELECTOR_CHART_DISTRIBUTION = "canvas#pie-chart";

    private static $LABEL_FORM_TOGGLE_EXPENSEINCOME_DEFAULT = "Expense";
    private static $LABEL_FORM_BUTTON_GENERATE = "Generate Chart";
    private static $LABEL_NO_STATS_DATA = "No data available";

    private static $VUE_KEY_STANDARDISEDATA = "standardiseData";

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->_account_or_account_type_toggling_selector_label_id = 'distribution-chart';
    }

    /**
     * @throws Throwable
     *
     * @group stats-distribution-1
     * test 1/25
     */
    public function testSelectDistributionSidebarOption(){
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
     * @throws Throwable
     *
     * @group stats-distribution-1
     * test 2/25
     */
    public function testFormHasCorrectElements(){
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts){
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionDistribution($browser);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_DISTRIBUTION)
                ->with(self::$SELECTOR_STATS_FORM_DISTRIBUTION, function(Browser $form) use ($accounts){
                    // account/account-type selector
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                    // expense/income - switch
                    $class_switch_core = ".v-switch-core";
                    $color_switch_default = "#B5B5B5";
                    $form
                        ->assertVisible(self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME)
                        ->assertSeeIn(self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME, self::$LABEL_FORM_TOGGLE_EXPENSEINCOME_DEFAULT);
                    $this->assertElementColour($form, self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME.' '.$class_switch_core, $color_switch_default);

                    // bulma date-picker
                    $this->assertDefaultStateBulmaDatePicker($form);

                    // button
                    $form
                        ->assertVisible(self::$SELECTOR_STATS_FORM_BUTTON_GENERATE)
                        ->assertSeeIn(self::$SELECTOR_STATS_FORM_BUTTON_GENERATE, self::$LABEL_FORM_BUTTON_GENERATE);
                    $button_classes = $form->attribute(self::$SELECTOR_STATS_FORM_BUTTON_GENERATE, 'class');
                    $this->assertContains('is-primary', $button_classes);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-distribution-1
     * test 3/25
     */
    public function testDefaultDataResultsArea(){
        $this->browse(function(Browser $browser){
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionDistribution($browser);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_RESULTS_AREA)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA);
        });
    }

    public function providerGenerateDistributionChart(){
        $previous_year_start = date("Y-01-01", strtotime('-1 year'));
        $today = date("Y-m-d");
        //[$datepicker_start, $datepicker_end, $is_account_switch_toggled, $is_expense_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available]
        return [
            //  default state of account/account-types; expense; default date range
            [null, null, false, false, false, false],                   // test 4/25
            // default state of account/account-types; income; default date range
            [null, null, false, true, false, false],                    // test 5/25
            // default state of account/account-types; expense; date range a year past to today
            [$previous_year_start, $today, false, false, false, false], // test 6/25
            // default state of account/account-types; income; date range a year past to today
            [$previous_year_start, $today, false, true, false, false],  // test 7/25
            // random account; expense; date range a year past to today
            [$previous_year_start, $today, false, false, true, false],  // test 8/25
            // random account; income; date range a year past to today
            [$previous_year_start, $today, false, true, true, false],   // test 9/25
            // random account-type; expense; date range a year past to today
            [$previous_year_start, $today, true, false, true, false],   // test 10/25
            // random account-type; income; date range a year past to today
            [$previous_year_start, $today, true, true, true, false],    // test 11/25
            // random disabled account; expense; date range a year past to today
            [$previous_year_start, $today, false, false, true, true],   // test 12/25
            // random disabled account; income; date range a year past to today
            [$previous_year_start, $today, false, true, true, true],    // test 13/25
            // random disabled account-type; expense; date range a year past to today
            [$previous_year_start, $today, true, false, true, true],    // test 14/25
            // random disabled account-type; income; date range a year past to today
            [$previous_year_start, $today, true, true, true, true],     // test 15/25
        ];
    }

    /**
     * @dataProvider providerGenerateDistributionChart
     *
     * @param string $datepicker_start
     * @param string $datepicker_end
     * @param bool $is_account_switch_toggled
     * @param bool $is_expense_switch_toggled
     * @param bool $is_random_selector_value
     * @param bool $are_disabled_select_options_available
     *
     * @throws Throwable
     *
     * @group stats-distribution-1
     * test (see provider)/25
     */
    public function testGenerateDistributionChart($datepicker_start, $datepicker_end, $is_account_switch_toggled, $is_expense_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available){
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());
        $tags = collect($this->getApiTags());

        $this->setDatabaseStateInjectionPermission(self::$ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION);

        $this->browse(function (Browser $browser) use ($datepicker_start, $datepicker_end, $is_account_switch_toggled, $is_expense_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types, $tags){
            $filter_data = [];

            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionDistribution($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_DISTRIBUTION)
                ->with(self::$SELECTOR_STATS_FORM_DISTRIBUTION, function(Browser $form) use ($is_expense_switch_toggled, $is_account_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types, &$filter_data, $datepicker_start, $datepicker_end){
                    if($are_disabled_select_options_available){
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }

                    if($is_account_switch_toggled){
                        // switch to account-types
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = $is_random_selector_value ? $account_types->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = $is_random_selector_value ? $accounts->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    }
                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_account_switch_toggled, $account_or_account_type_id);

                    if($is_expense_switch_toggled){
                        // expense/income - switch
                        $form->click(self::$SELECTOR_STATS_FORM_TOGGLE_EXPENSEINCOME);
                    }
                    $filter_data = $this->generateFilterArrayElementExpense($filter_data, !$is_expense_switch_toggled);

                    if(!is_null($datepicker_start) && !is_null($datepicker_end)){
                        $this->setDateRange($form, $datepicker_start, $datepicker_end);
                    } else {
                        $datepicker_start = date('Y-m-01');
                        $datepicker_end = date('Y-m-t');
                    }
                    $filter_data = $this->generateFilterArrayElementDatepicker($filter_data, $datepicker_start, $datepicker_end);

                    $form->click(self::$SELECTOR_STATS_FORM_BUTTON_GENERATE);
                });

            $entries = $this->getBatchedFilteredEntries($filter_data);

            $this->waitForLoadingToStop($browser);
            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA)
                ->with(self::$SELECTOR_STATS_RESULTS_AREA, function(Browser $stats_results_area){
                    $stats_results_area->assertVisible(self::$SELECTOR_CHART_DISTRIBUTION);
                })
                ->assertVue(self::$VUE_KEY_STANDARDISEDATA, $this->standardiseData($entries, $tags), self::$SELECTOR_STATS_DISTRIBUTION);
        });
    }

    /**
     * @param Collection $entries
     * @param Collection $tags
     * @return array
     */
    private function standardiseData($entries, $tags){
        $standardised_chart_data = [];

        foreach($entries as $entry){
            if(empty($entry['tags'])){
                $entry['tags'][] = 0;
            }
            foreach($entry['tags'] as $tag_id){
                $key = $tag_id === 0 ? 'untagged' : $tags->where('id', $tag_id)->pluck('name')->first();

                if(!isset($standardised_chart_data[$key])){
                    $standardised_chart_data[$key] = ['x'=>$key,'y'=>0];
                }
                $standardised_chart_data[$key]['y'] += $entry['entry_value'];
                $standardised_chart_data[$key]['y'] = round($standardised_chart_data[$key]['y'], 2);
            }
        }
        $x_col = array_column($standardised_chart_data, 'x');
        array_multisort($x_col, SORT_ASC, $standardised_chart_data);
        return array_values($standardised_chart_data);
    }

}
