<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\BulmaDatePicker as DuskTraitBulmaDatePicker;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Collection;
use Tests\Browser\Pages\StatsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

/**
 * Class StatsSummaryTest
 *
 * @package Tests\Browser
 *
 * @group stats
 * @group stats-summary
 */
class StatsSummaryTest extends DuskTestCase {

    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBatchFilterEntries;
    use DuskTraitBulmaDatePicker;
    use DuskTraitLoading;
    use DuskTraitStatsSidePanel;

    private static $SELECTOR_STATS_FORM_SUMMARY = "#stats-form-summary";
    private static $SELECTOR_BUTTON_GENERATE = '.generate-stats';
    private static $SELECTOR_STATS_RESULTS_AREA = '.stats-results-summary';

    private static $LABEL_GENERATE_TABLE_BUTTON = "Generate Tables";
    private static $LABEL_NO_STATS_DATA = 'No data available';
    private static $LABEL_TABLE_NAME_TOTAL = 'Total Income/Expenses';
    private static $LABEL_TABLE_NAME_TOP = 'Top 10 income/expense entries';

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->_account_or_account_type_toggling_selector_label_id = 'summary-chart';
    }

    /**
     * @throws Throwable
     *
     * @group stats-summary-1
     * test 1/25
     */
    public function testCorrectSidebarOptionSelectedIsSummaryByDefault(){
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_SIDE_PANEL);
            $this->assertStatsSidePanelHeading($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY);;
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-summary-1
     * test 2/25
     */
    public function testFormHasCorrectElements(){
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts){
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY)
                ->with(self::$SELECTOR_STATS_FORM_SUMMARY, function(Browser $form) use ($accounts){
                    // account/account-type selector
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                    // bulma date-picker
                    $this->assertDefaultStateBulmaDatePicker($form);

                    // button
                    $form
                        ->assertVisible(self::$SELECTOR_BUTTON_GENERATE)
                        ->assertSeeIn(self::$SELECTOR_BUTTON_GENERATE, self::$LABEL_GENERATE_TABLE_BUTTON);
                    $button_classes = $form->attribute(self::$SELECTOR_BUTTON_GENERATE, 'class');
                    $this->assertContains('is-primary', $button_classes);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-summary-1
     * test 3/25
     */
    public function testDefaultDataResultsArea(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_RESULTS_AREA)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA);
        });
    }

    public function providerTestGenerateStatsTables(){
        $previous_year_start = date("Y-01-01", strtotime('-1 year'));
        $today = date("Y-m-d");
        //[$datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available]
        return [
            // defaults account/account-type & date-picker values
            [null, null, false, false, false],  // test 4/25
            // date-picker previous year start to present & default account/account-type
            [$previous_year_start, $today, false, false, false],    // test 5/25
            // date-picker previous year start to present & random account
            [$previous_year_start, $today, false, true, false],     // test 6/25
            // date-picker previous year start to present & random account-type
            [$previous_year_start, $today, true, true, false],      // test 7/25
            // date-picker previous year start to present & random disabled account
            [$previous_year_start, $today, false, true, false],     // test 8/25
            // date-picker previous year start to present & random disabled account-type
            [$previous_year_start, $today, true, true, false],      // test 9/25
        ];
    }

    /**
     * @dataProvider providerTestGenerateStatsTables
     *
     * @param string $datepicker_start
     * @param string $datepicker_end
     * @param bool $is_switch_toggled
     * @param bool $is_random_selector_value
     * @param bool $are_disabled_select_options_available
     *
     * @throws Throwable
     *
     * @group stats-summary-1
     * test (see provider)/25
     */
    public function testGenerateStatsTables($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available){
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());

        $this->browse(function (Browser $browser) use ($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types){
            $filter_data = [];

            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY)
                ->with(self::$SELECTOR_STATS_FORM_SUMMARY, function(Browser $form) use ($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, &$filter_data, $accounts, $account_types){
                    if($are_disabled_select_options_available){
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }
                    if($is_switch_toggled){
                        // switch to account-types
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    }
                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_switch_toggled, $account_or_account_type_id);

                    if(!is_null($datepicker_start) && !is_null($datepicker_end)){
                        $this->setDateRange($form, $datepicker_start, $datepicker_end);
                    } else {
                        // default values
                        $datepicker_start = date('Y-m-01');
                        $datepicker_end = date('Y-m-t');
                    }
                    $filter_data = $this->generateFilterArrayElementDatepicker($filter_data, $datepicker_start, $datepicker_end);

                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $this->waitForLoadingToStop($browser);
            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA)

                ->with(self::$SELECTOR_STATS_RESULTS_AREA, function(Browser $stats_results) use ($filter_data, $account_types, $accounts){
                    $selector_table_total_income_expense = 'table:nth-child(1)';
                    $selector_table_top_10_income_expense = 'table:nth-child(3)';
                    $selector_table_label = 'caption';
                    $selector_table_body_rows = 'tbody tr';

                    $entries = $this->getBatchedFilteredEntries($filter_data);
                    $stats_results
                        ->assertVisible($selector_table_total_income_expense)
                        ->with($selector_table_total_income_expense, function(Browser $table) use ($selector_table_label, $selector_table_body_rows, $entries, $accounts, $account_types){
                            $totals = $this->getTotalIncomeExpenses($entries, $accounts, $account_types);

                            $table->assertSeeIn($selector_table_label, self::$LABEL_TABLE_NAME_TOTAL);
                            $table_rows = $table->elements($selector_table_body_rows);
                            $this->assertGreaterThanOrEqual(1, count($table_rows));
                            $this->assertGreaterThanOrEqual(1, count($totals));
                            $this->assertSameSize($totals, $table_rows, "'".self::$LABEL_TABLE_NAME_TOTAL."' table row count does not match expected totals:".print_r($totals, true));

                            $selector_cell_total_income = 'td:nth-child(1)';
                            $selector_cell_total_expense = 'td:nth-child(2)';
                            $selector_cell_total_currency = 'td:nth-child(3)';
                            foreach($table_rows as $table_row){
                                //  income | expense | currency
                                $currency_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_total_currency))->getText();
                                $income_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_total_income))->getText();
                                $this->assertEquals($totals[$currency_cell_text]['income'], $income_cell_text);
                                $expense_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_total_expense))->getText();
                                $this->assertEquals($totals[$currency_cell_text]['expense'], $expense_cell_text);
                            }
                        })
                        ->assertVisible($selector_table_top_10_income_expense)
                        ->with($selector_table_top_10_income_expense, function(Browser $table) use ($selector_table_label, $selector_table_body_rows, $entries){
                            $top_entries = $this->getTop10IncomeExpenses($entries);

                            $table->assertSeeIn($selector_table_label, self::$LABEL_TABLE_NAME_TOP);
                            $table_rows = $table->elements($selector_table_body_rows);
                            $this->assertGreaterThanOrEqual(1, count($table_rows));
                            $this->assertGreaterThanOrEqual(1, count($top_entries));
                            $this->assertLessThanOrEqual(10, count($table_rows));
                            $this->assertLessThanOrEqual(10, count($top_entries));
                            $this->assertSameSize($top_entries, $table_rows, "'".self::$LABEL_TABLE_NAME_TOP."' table row count does not match expected totals:".print_r($top_entries, true));

                            $selector_cell_index = 'td:nth-child(1)';
                            $selector_cell_income_memo = 'td:nth-child(2)';
                            $selector_cell_income_value = 'td:nth-child(3)';
                            $selector_cell_income_date = 'td:nth-child(4)';
                            $selector_cell_expense_memo = 'td:nth-child(5)';
                            $selector_cell_expense_value = 'td:nth-child(6)';
                            $selector_cell_expense_date = 'td:nth-child(7)';
                            foreach($table_rows as $table_row){
                                //  i | income memo | income value | expense memo | expense value
                                $index_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_index))->getText();
                                $error_message_postfix = "index:".$index_cell_text.' '.print_r($top_entries[$index_cell_text], true);

                                $income_memo_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_income_memo))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['income_memo'], $income_memo_cell_text, "income_memo values don't match\n".$error_message_postfix);

                                $income_value_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_income_value))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['income_value'], $income_value_text, "income_value values don't match\n".$error_message_postfix);

                                $income_date_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_income_date))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['income_date'], $income_date_text, "income_date values don't match\n".$error_message_postfix);

                                $expense_memo_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_expense_memo))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['expense_memo'], $expense_memo_cell_text, "expense_memo don't match\n".$error_message_postfix);

                                $expense_value_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_expense_value))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['expense_value'], $expense_value_text, "expense_value don't match\n".$error_message_postfix);

                                $expense_date_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_expense_date))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['expense_date'], $expense_date_text, "expense_date don't match\n".$error_message_postfix);
                             }
                        });
            });
        });
    }

    public function providerGeneratingADifferentChartWontCauseSummaryTablesToBecomeVisible(){
        return [
            // [$side_panel_selector, $stats_form_selector, $stats_results_selector]
            'trending'=>[self::$SELECTOR_STATS_SIDE_PANEL_OPTION_TRENDING, '#stats-form-trending', '.stats-results-trending'],
            'tags'=>[self::$SELECTOR_STATS_SIDE_PANEL_OPTION_TAGS, '#stats-form-tags', '.stats-results-tags'],
            'distribution'=>[self::$SELECTOR_STATS_SIDE_PANEL_OPTION_DISTRIBUTION, '#stats-form-distribution', '.stats-results-distribution'],
        ];
    }

    /**
     * @dataProvider providerGeneratingADifferentChartWontCauseSummaryTablesToBecomeVisible
     * @param $side_panel_selector
     * @param $stats_form_selector
     * @param $stats_results_selector
     *
     * @throws Throwable
     */
    public function testGeneratingADifferentChartWontCauseSummaryTablesToBecomeVisible($side_panel_selector, $stats_form_selector, $stats_results_selector){
        $this->browse(function (Browser $browser) use ($side_panel_selector, $stats_form_selector, $stats_results_selector){
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY);

            $this->clickStatsSidePanelOption($browser, $side_panel_selector);
            $browser
                ->assertVisible($stats_form_selector)
                ->with($stats_form_selector, function(Browser $form){
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });
            $this->waitForLoadingToStop($browser);
            $browser->assertDontSeeIn($stats_results_selector, self::$LABEL_NO_STATS_DATA);

            $this->clickStatsSidePanelOptionSummary($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA);
        });

    }

    /**
     * @param Collection $entries
     * @param Collection $accounts
     * @param Collection $account_types
     * @return array
     */
    private function getTotalIncomeExpenses($entries, $accounts, $account_types){
        $totals = [];
        $currencies = $accounts->unique('currency')->pluck('currency')->all();
        foreach($currencies as $currency){
            $totals[$currency]['income'] = 0;
            $totals[$currency]['expense'] = 0;
        }

        foreach($entries as $entry){
            $account_id = $account_types->where('id', $entry['account_type_id'])->pluck('account_id')->first();
            $account = $accounts->where('id', $account_id)->first();
            if($entry['expense'] == 1){
                $income_expense = 'expense';
            } else {
                $income_expense = 'income';
            }
            $totals[$account['currency']][$income_expense] += $entry['entry_value'];
        }

        // purge any 0 values
        foreach($totals as $currency=>$total){
            if($total['income'] === 0 && $total['expense'] === 0){
                unset($totals[$currency]);
            }
        }
        return $totals;
    }

    /**
     * @param Collection $entries
     * @return array
     */
    private function getTop10IncomeExpenses($entries){
        $top_income_entries = $entries->where('expense', 0)->sortByDesc($this->sortCallable())->values();
        $top_expense_entries = $entries->where('expense', 1)->sortByDesc($this->sortCallable())->values();

        $top_entries = [];
        for($i=0; $i<10; $i++){
            if(empty($top_income_entries->get($i)) && empty($top_expense_entries->get($i))){
                break;
            }

            $top_entries[$i+1] = [
                'income_memo'=>!empty($top_income_entries->get($i)) ? $top_income_entries->get($i)['memo'] : '',
                'income_value'=>!empty($top_income_entries->get($i)) ? $top_income_entries->get($i)['entry_value'] : '',
                'income_date'=>!empty($top_income_entries->get($i)) ? $top_income_entries->get($i)['entry_date'] : '',
                'expense_memo'=>!empty($top_expense_entries->get($i)) ? $top_expense_entries->get($i)['memo'] : '',
                'expense_value'=>!empty($top_expense_entries->get($i)) ? $top_expense_entries->get($i)['entry_value'] : '',
                'expense_date'=>!empty($top_expense_entries->get($i)) ? $top_expense_entries->get($i)['entry_date'] : ''
            ];
        }
        return $top_entries;
    }

    /**
     * @link https://laracasts.com/discuss/channels/laravel/collections-passing-a-class-method-name-not-a-closure-to-the-map-method?page=1#reply=456138
     * @link https://stackoverflow.com/a/25451441/4152012
     *
     * @return string
     */
    private function sortCallable(){
        return function($entry){
            return sprintf("%010s %s %d", $entry['entry_value'], $entry['entry_date'], $entry['id']);
        };
    }

}
