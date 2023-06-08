<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\StatsDateRange as DuskTraitStatsDateRange;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use Brick\Money\Money;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Support\Collection;
use Tests\Browser\Pages\StatsPage;
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
class StatsSummaryTest extends StatsBase {
    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBatchFilterEntries;
    use DuskTraitStatsDateRange;
    use DuskTraitStatsSidePanel;

    private static $LABEL_GENERATE_TABLE_BUTTON = "Generate Tables";
    private static $LABEL_TABLE_NAME_TOTAL = 'Total Income/Expenses';
    private static $LABEL_TABLE_NAME_TOP = 'Top 10 income/expense entries';

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $chart_designation = 'summary-chart';
        $this->_account_or_account_type_toggling_selector_id_label = $chart_designation;
        $this->date_range_chart_name = $chart_designation;
        $this->include_transfers_chart_name = $chart_designation;
    }

    /**
     * @throws Throwable
     *
     * @group stats-summary-1
     * test 1/20
     */
    public function testCorrectSidebarOptionSelectedIsSummaryByDefault() {
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_SIDE_PANEL);
            $this->assertStatsSidePanelHeading($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY);
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-summary-1
     * test 2/20
     */
    public function testFormHasCorrectElements() {
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY)
                ->within(self::$SELECTOR_STATS_FORM_SUMMARY, function(Browser $form) use ($accounts) {
                    // account/account-type selector
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                    // date range
                    $this->assertDefaultStateDateRange($form);

                    // button
                    $form
                        ->assertVisible(self::$SELECTOR_BUTTON_GENERATE)
                        ->assertSeeIn(self::$SELECTOR_BUTTON_GENERATE, self::$LABEL_GENERATE_TABLE_BUTTON);
                    $this->assertElementBackgroundColor($form, self::$SELECTOR_BUTTON_GENERATE, $this->tailwindColors->blue(600));
                    $this->assertElementTextColor($form, self::$SELECTOR_BUTTON_GENERATE, $this->tailwindColors->white());
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-summary-1
     * test 3/20
     */
    public function testDefaultDataResultsArea() {
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_RESULTS_SUMMARY)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_SUMMARY, self::$LABEL_NO_STATS_DATA);
            $this->assertIncludeTransfersCheckboxButtonNotVisible($browser);
        });
    }

    public function providerTestGenerateStatsTables(): array {
        //[$datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available]
        return [
            // defaults account/account-type & date-picker values
            [null, null, false, false, false, false],  // test 4/20
            // defaults account/account-type & date-picker values & include transfers
            [null, null, false, false, false, true],  // test 5/20
            // date-picker previous year start to present & default account/account-type
            [$this->previous_year_start, $this->today, false, false, false, false],    // test 6/20
            // date-picker previous year start to present & default account/account-type & include transfers
            [$this->previous_year_start, $this->today, false, false, false, true],    // test 7/20
            // date-picker previous year start to present & random account
            [$this->previous_year_start, $this->today, false, true, false, false],     // test 8/20
            // date-picker previous year start to present & random account & include transfers
            [$this->previous_year_start, $this->today, false, true, false, true],     // test 9/20
            // date-picker previous year start to present & random account-type
            [$this->previous_year_start, $this->today, true, true, false, false],     // test 10/20
            // date-picker previous year start to present & random account-type & include transfers
            [$this->previous_year_start, $this->today, true, true, false, true],      // test 11/20
            // date-picker previous year start to present & random disabled account
            [$this->previous_year_start, $this->today, false, true, false, false],    // test 12/20
            // date-picker previous year start to present & random disabled account & include transfers
            [$this->previous_year_start, $this->today, false, true, false, true],     // test 13/20
            // date-picker previous year start to present & random disabled account-type
            [$this->previous_year_start, $this->today, true, true, false, false],     // test 14/20
            // date-picker previous year start to present & random disabled account-type & include transfers
            [$this->previous_year_start, $this->today, true, true, false, true],      // test 15/20
            // defaults account/account-type & date-picker values; date range today only
            [$this->today, $this->today, false, false, false, false],                 // test 16/20
            // defaults account/account-type & date-picker values; date range today only; include transfers
            [$this->today, $this->today, false, false, false, true],                  // test 17/20
        ];
    }

    /**
     * @dataProvider providerTestGenerateStatsTables
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
     * @group stats-summary-1
     * test (see provider)/20
     */
    public function testGenerateStatsTables(?string $datepicker_start, ?string $datepicker_end, bool $is_switch_toggled, bool $is_random_selector_value, bool $are_disabled_select_options_available, bool $include_transfers) {
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());

        $this->browse(function(Browser $browser) use ($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $include_transfers, $accounts, $account_types) {
            $filter_data = [];

            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY)
                ->within(self::$SELECTOR_STATS_FORM_SUMMARY, function(Browser $form) use ($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, &$filter_data, $accounts, $account_types) {
                    if ($are_disabled_select_options_available) {
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }
                    if ($is_switch_toggled) {
                        // switch to account-types
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    }
                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_switch_toggled, $account_or_account_type_id);

                    if (!is_null($datepicker_start)) {
                        $this->setDateRangeDate($form, 'start', $datepicker_start);
                    } else {
                        $datepicker_start = $this->month_start; // default value
                    }
                    if (!is_null($datepicker_end)) {
                        $this->setDateRangeDate($form, 'end', $datepicker_end);
                    } else {
                        $datepicker_end = $this->month_end; // default value
                    }

                    $filter_data = $this->generateFilterArrayElementDatepicker($filter_data, $datepicker_start, $datepicker_end);

                    $this->generateEntryFromFilterData($filter_data, $this->getName());
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $this->waitForLoadingToStop($browser);
            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_SUMMARY, self::$LABEL_NO_STATS_DATA)

                ->within(self::$SELECTOR_STATS_RESULTS_SUMMARY, function(Browser $stats_results) use ($filter_data, $include_transfers, $account_types, $accounts) {
                    $selector_table_total_income_expense = 'table:nth-child(2)';
                    $selector_table_top_10_income_expense = 'table:nth-child(4)';
                    $selector_table_label = 'caption';
                    $selector_table_body_rows = 'tbody tr';

                    $entries = $this->getBatchedFilteredEntries($filter_data);
                    $entries = $this->filterTransferEntries($entries, $include_transfers);

                    $this->assertIncludeTransfersButtonDefaultState($stats_results);
                    if ($include_transfers) {
                        $this->clickIncludeTransfersCheckboxButton($stats_results);
                        $this->assertIncludesTransfersCheckboxButtonStateActive($stats_results);
                    }

                    $stats_results
                        ->assertVisible($selector_table_total_income_expense)
                        ->within($selector_table_total_income_expense, function(Browser $table) use ($selector_table_label, $selector_table_body_rows, $entries, $accounts, $account_types) {
                            $totals = $this->getTotalIncomeExpenses($entries, $accounts, $account_types);

                            $table->assertSeeIn($selector_table_label, self::$LABEL_TABLE_NAME_TOTAL);
                            $table_rows = $table->elements($selector_table_body_rows);
                            $this->assertGreaterThanOrEqual(1, count($table_rows));
                            $this->assertGreaterThanOrEqual(1, count($totals));
                            $this->assertSameSize($totals, $table_rows, "'".self::$LABEL_TABLE_NAME_TOTAL."' table row count does not match expected totals: ".print_r($totals, true));

                            $selector_cell_total_income = 'td:nth-child(1) span:nth-child(2)';
                            $selector_cell_total_expense = 'td:nth-child(2) span:nth-child(2)';
                            $selector_cell_total_currency = 'td:nth-child(3)';
                            foreach ($table_rows as $table_row) {
                                //  income | expense | currency
                                $currency_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_total_currency))->getText();
                                $income_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_total_income))->getText();
                                $this->assertTrue($totals[$currency_cell_text]['income']->isEqualTo($income_cell_text));
                                $expense_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_total_expense))->getText();
                                $this->assertTrue($totals[$currency_cell_text]['expense']->isEqualTo($expense_cell_text));
                            }
                        })
                        ->assertVisible($selector_table_top_10_income_expense)
                        ->within($selector_table_top_10_income_expense, function(Browser $table) use ($selector_table_label, $selector_table_body_rows, $entries) {
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
                            $selector_cell_expense_memo = 'td:nth-child(4)';
                            $selector_cell_expense_value = 'td:nth-child(5)';
                            foreach ($table_rows as $table_row) {
                                //  i | income memo (w/ tooltip) | income value | expense memo (w/ tooltip) | expense value
                                $index_cell_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_index))->getText();
                                $error_message_postfix = "index:".$index_cell_text.' '.print_r($top_entries[$index_cell_text], true);

                                $income_memo_element = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_income_memo));
                                $income_memo_cell_text = $income_memo_element->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['income_memo'], $income_memo_cell_text, "income_memo values don't match\n".$error_message_postfix);

                                if (!empty($income_memo_cell_text)) {
                                    $this->assertTooltip($table, $income_memo_element, $top_entries[$index_cell_text]['income_date']);
                                }

                                $income_value_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_income_value))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['income_value'], $income_value_text, "income_value values don't match\n".$error_message_postfix);

                                $expense_memo_element = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_expense_memo));
                                $expense_memo_cell_text = $expense_memo_element->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['expense_memo'], $expense_memo_cell_text, "expense_memo don't match\n".$error_message_postfix);

                                if (!empty($expense_memo_cell_text)) {
                                    $this->assertTooltip($table, $expense_memo_element, $top_entries[$index_cell_text]['expense_date']);
                                }

                                $expense_value_text = $table_row->findElement(WebDriverBy::cssSelector($selector_cell_expense_value))->getText();
                                $this->assertEquals($top_entries[$index_cell_text]['expense_value'], $expense_value_text, "expense_value don't match\n".$error_message_postfix);
                            }
                        });
                });
        });
    }

    /**
     * @param Collection $entries
     * @param Collection $accounts
     * @param Collection $account_types
     * @return array
     */
    private function getTotalIncomeExpenses($entries, $accounts, $account_types): array {
        $totals = [];
        $currencies = $accounts->unique('currency')->pluck('currency')->all();
        foreach ($currencies as $currency) {
            $totals[$currency]['income'] = Money::zero($currency);
            $totals[$currency]['expense'] = Money::zero($currency);
        }

        foreach ($entries as $entry) {
            $account_id = $account_types->where('id', $entry['account_type_id'])->pluck('account_id')->first();
            $account = $accounts->where('id', $account_id)->first();
            if ($entry['expense'] == 1) {
                $income_expense = 'expense';
            } else {
                $income_expense = 'income';
            }
            $totals[$account['currency']][$income_expense] = $totals[$account['currency']][$income_expense]->plus($entry['entry_value']);
        }

        // purge any 0 values
        foreach ($totals as $currency=>$total) {
            if ($total['income']->isZero() && $total['expense']->isZero()) {
                unset($totals[$currency]);
            }
        }
        return $totals;
    }

    /**
     * @param Collection $entries
     * @return array
     */
    private function getTop10IncomeExpenses($entries): array {
        $limit = 10;
        $top_income_entries = $entries->where('expense', 0)->sortByDesc($this->sortCallable())->take($limit)->values();
        $top_expense_entries = $entries->where('expense', 1)->sortByDesc($this->sortCallable())->take($limit)->values();

        $top_entries = [];
        $empty_top_entry = ['memo'=>'', 'entry_value'=>'', 'entry_date'=>''];
        for ($i=0; $i<$limit; $i++) {
            $top_income = $top_income_entries->get($i) ?: $empty_top_entry;
            $top_expense = $top_expense_entries->get($i) ?: $empty_top_entry;
            if ($top_expense == $empty_top_entry && $top_income == $empty_top_entry) {
                break;
            }

            $top_entries[$i+1] = [
                'income_memo'=>$top_income['memo'],
                'income_value'=>$top_income['entry_value'],
                'income_date'=>$top_income['entry_date'],
                'expense_memo'=>$top_expense['memo'],
                'expense_value'=>$top_expense['entry_value'],
                'expense_date'=>$top_expense['entry_date'],
            ];
        }
        return $top_entries;
    }

    /**
     * This method takes a lot of code from predefined Browser methods
     * This allows us to do some work around testing without having to worry about css selector chaining
     *
     * @param Browser $browser
     * @param RemoteWebElement $element
     * @param string $tooltip_text
     */
    private function assertTooltip(Browser $browser, $element, string $tooltip_text) {
        $browser->driver->getMouse()->mouseMove($element->getCoordinates());    // move mouse over element
        $tooltip_id = $element->getAttribute('aria-describedby');    // get the tooltip element id
        $this->assertNotEmpty($tooltip_id);
        $browser->pause(self::$WAIT_ONE_SECOND_IN_MILLISECONDS);

        $selector_tooltip = "#".$tooltip_id;
        $this->assertTrue(
            $browser->resolver->findOrFail($selector_tooltip)->isDisplayed(),
            "Element [$selector_tooltip] is not visible."
        );

        $tooltip_text_from_element = $browser->text('#'.$tooltip_id);
        $this->assertStringContainsString($tooltip_text, $tooltip_text_from_element);
    }

    /**
     * @link https://laracasts.com/discuss/channels/laravel/collections-passing-a-class-method-name-not-a-closure-to-the-map-method?page=1#reply=456138
     * @link https://stackoverflow.com/a/25451441/4152012
     */
    private function sortCallable() {
        return static function($entry) {
            return sprintf("%010.2f %s %05d", $entry['entry_value'], $entry['entry_date'], $entry['id']);
        };
    }

}
