<?php

namespace Tests\Browser;

use App\Http\Controllers\Api\EntryController;
use App\Traits\Tests\AccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BulmaDatePicker;
use App\Traits\Tests\Dusk\Loading;
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

    use AccountOrAccountTypeTogglingSelector;
    use BulmaDatePicker;
    use Loading;

    private $_selector_stats_form_summary = "#stats-form-summary";
    private $_selector_button_generate = '.generate-stats';
    private $_selector_stats_results_area = '.stats-results-summary';

    private $_label_no_stats_data = 'No data available';

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->_id_label = 'summary-chart';
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
                ->assertVisible('.panel')
                ->within('.panel', function(Browser $sidepanel){
                    $sidepanel
                        ->assertVisible('.panel-heading:first-child')
                        ->assertSeeIn('.panel-heading:first-child', "Stats")
                        ->assertVisible('.panel-heading+.panel-block')
                        ->assertSeeIn('.panel-heading+.panel-block', 'Summary');

                    $classes = $sidepanel->attribute('.panel-heading+.panel-block', 'class');
                    $this->assertContains('is-active', $classes);
                });
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
                ->assertVisible($this->_selector_stats_form_summary)
                ->with($this->_selector_stats_form_summary, function(Browser $form) use ($accounts){
                    // account/account-type selector
                    $this->_id_label = 'summary-chart';
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                    // bulma date-picker
                    $this->assertDefaultStateBulmaDatePicker($form);

                    // button
                    $label_generate_data_button = 'Generate Tables';
                    $form
                        ->assertVisible($this->_selector_button_generate)
                        ->assertSeeIn($this->_selector_button_generate, $label_generate_data_button);
                    $button_classes = $form->attribute($this->_selector_button_generate, 'class');
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
                ->assertVisible($this->_selector_stats_results_area)
                ->assertSeeIn($this->_selector_stats_results_area, $this->_label_no_stats_data);
        });
    }

    public function providerTestGenerateStatsTables(){
        $previous_year_start = date("Y-01-01", strtotime('-1 year'));
        $today = date("Y-m-d");
        return [
            //[$datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available]
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
            $account_or_account_type_id = null;

            $browser
                ->visit(new StatsPage())
                ->assertVisible($this->_selector_stats_form_summary)
                ->with($this->_selector_stats_form_summary, function(Browser $form) use (&$datepicker_start, &$datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, &$account_or_account_type_id, $accounts, $account_types){
                    if($are_disabled_select_options_available){
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }
                    if($is_switch_toggled){
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    } else {
                        $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    }
                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);

                    if(!is_null($datepicker_start) && !is_null($datepicker_end)){
                        $this->setDateRange($form, $datepicker_start, $datepicker_end);
                    } else {
                        // default values
                        $datepicker_start = date('Y-m-01');
                        $datepicker_end = date('Y-m-t');
                    }

                    $form->click($this->_selector_button_generate);
                });

            $this->waitForLoadingToStop($browser);
            $browser->assertDontSeeIn($this->_selector_stats_results_area, $this->_label_no_stats_data);

            $browser->with($this->_selector_stats_results_area, function(Browser $stats_results) use ($datepicker_start, $datepicker_end, $is_switch_toggled, &$account_or_account_type_id, $account_types, $accounts){
                $selector_table_total_income_expense = 'table:nth-child(1)';
                $selector_table_top_10_income_expense = 'table:nth-child(3)';
                $selector_table_label = 'caption';
                $selector_table_body_rows = 'tbody tr';

                $entries = $this->filterEntries($datepicker_start, $datepicker_end, $account_or_account_type_id, $is_switch_toggled);
                $stats_results
                    ->assertVisible($selector_table_total_income_expense)
                    ->with($selector_table_total_income_expense, function(Browser $table) use ($selector_table_label, $selector_table_body_rows, $entries, $accounts, $account_types, $account_or_account_type_id, $datepicker_start, $datepicker_end, $is_switch_toggled){
                        $totals = $this->getTotalIncomeExpenses($entries, $accounts, $account_types);

                        $table->assertSeeIn($selector_table_label, "Total Income/Expenses");
                        $table_rows = $table->elements($selector_table_body_rows);
                        $this->assertGreaterThanOrEqual(1, count($table_rows));
                        $this->assertGreaterThanOrEqual(1, count($totals));
                        $this->assertSameSize($totals, $table_rows, "'Total Income/Expense' table row count does not match expected totals:".print_r($totals, true));

                        foreach($table_rows as $table_row){
                            //  income | expense | currency
                            $currency_cell_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(3)'))->getText();
                            $income_cell_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(1)'))->getText();
                            $this->assertEquals($totals[$currency_cell_text]['income'], $income_cell_text);
                            $expense_cell_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(2)'))->getText();
                            $this->assertEquals($totals[$currency_cell_text]['expense'], $expense_cell_text);
                        }
                    })
                    ->assertVisible($selector_table_top_10_income_expense)
                    ->with($selector_table_top_10_income_expense, function(Browser $table) use ($selector_table_label, $selector_table_body_rows, $entries){
                        $top_entries = $this->getTop10IncomeExpenses($entries);

                        $table->assertSeeIn($selector_table_label, "Top 10 income/expense entries");
                        $table_rows = $table->elements($selector_table_body_rows);
                        $this->assertGreaterThanOrEqual(1, count($table_rows));
                        $this->assertGreaterThanOrEqual(1, count($top_entries));
                        $this->assertLessThanOrEqual(10, count($table_rows));
                        $this->assertLessThanOrEqual(10, count($top_entries));
                        $this->assertSameSize($top_entries, $table_rows, "'Top 10 income/expense entries' table row count does not match expected totals:".print_r($top_entries, true));

                        foreach($table_rows as $table_row){
                            //  i | income memo | income value | expense memo | expense value
                            $index_cell_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(1)'))->getText();
                            $error_message_postfix = "index:".$index_cell_text.' '.print_r($top_entries[$index_cell_text], true);
                            $income_memo_cell_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(2)'))->getText();
                            $this->assertEquals($top_entries[$index_cell_text]['income_memo'], $income_memo_cell_text, "income_memo values don't match\n".$error_message_postfix);
                            $income_value_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(3)'))->getText();
                            $this->assertEquals($top_entries[$index_cell_text]['income_value'], $income_value_text, "income_value values don't match\n".$error_message_postfix);
                            $expense_memo_cell_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(4)'))->getText();
                            $this->assertEquals($top_entries[$index_cell_text]['expense_memo'], $expense_memo_cell_text, "expense_memo don't match\n".$error_message_postfix);
                            $expense_value_text = $table_row->findElement(WebDriverBy::cssSelector('td:nth-child(5)'))->getText();
                            $this->assertEquals($top_entries[$index_cell_text]['expense_value'], $expense_value_text, "expense_value don't match\n".$error_message_postfix);
                         }
                    });
            });
        });
    }

    /**
     * @param string $start_date
     * @param string $end_date
     * @param int $account_or_account_type_id
     * @param bool $is_switch_toggled
     * @return Collection
     */
    private function filterEntries($start_date, $end_date, $account_or_account_type_id, $is_switch_toggled){
        $filter_data = [
            EntryController::FILTER_KEY_START_DATE=>$start_date,
            EntryController::FILTER_KEY_END_DATE=>$end_date,
        ];

        if(!empty($account_or_account_type_id)){
            if($is_switch_toggled){
                $filter_data[EntryController::FILTER_KEY_ACCOUNT_TYPE] = $account_or_account_type_id;
            } else {
                $filter_data[EntryController::FILTER_KEY_ACCOUNT] = $account_or_account_type_id;
            }
        }

        return collect($this->removeCountFromApiResponse($this->getApiEntries(0, $filter_data)));
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
        $top_income_entries = $entries->where('expense', 0)->sortByDesc('entry_value')->values();
        $top_expense_entries = $entries->where('expense', 1)->sortByDesc('entry_value')->values();

        $top_entries = [];
        for($i=0; $i<10; $i++){
            if(empty($top_income_entries->get($i)) && empty($top_expense_entries->get($i))){
                break;
            }

            $top_entries[$i+1] = [
                'income_memo'=>!empty($top_income_entries->get($i)) ? $top_income_entries->get($i)['memo'] : '',
                'income_value'=>!empty($top_income_entries->get($i)) ? $top_income_entries->get($i)['entry_value'] : '',
                'expense_memo'=>!empty($top_expense_entries->get($i)) ? $top_expense_entries->get($i)['memo'] : '',
                'expense_value'=>!empty($top_expense_entries->get($i)) ? $top_expense_entries->get($i)['entry_value'] : ''
            ];
        }
        return $top_entries;
    }
}
