<?php

namespace Tests\Browser;

use App\Models\Entry;
use App\Traits\Tests\AssertElementColor;
use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\StatsDateRange as DuskTraitStatsDateRange;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use Laravel\Dusk\Browser;
use Illuminate\Support\Collection;
use Tests\Browser\Pages\StatsPage;

/**
 * Class StatsTagsTest
 *
 * @package Tests\Browser
 *
 * @group stats
 * @group stats-tags
 */
class StatsTagsTest extends StatsBase {
    use AssertElementColor;
    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBatchFilterEntries;
    use DuskTraitStatsDateRange;
    use DuskTraitStatsSidePanel;
    use DuskTraitTagsInput;

    private static $SELECTOR_STATS_TAGS = "#stats-tags";
    private static $SELECTOR_CHART_TAGS = 'canvas#bar-chart';
    // Selectors

    private static $VUE_KEY_STANDARDISEDATA = "standardiseData";
    // vue keys

    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $chart_designation = 'tags-chart';
        $this->_account_or_account_type_toggling_selector_id_label = $chart_designation;
        $this->date_range_chart_name = $chart_designation;
        $this->include_transfers_chart_name = $chart_designation;
    }

    public function setUp(): void {
        parent::setUp();
        $this->initTagsInputColors();
    }

    /**
     * @group stats-tags-1
     * test 1/20
     */
    public function testSelectTagsSidebarOption() {
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_SIDE_PANEL);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY);
            $this->clickStatsSidePanelOptionTags($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_TAGS);
        });
    }

    /**
     * @group stats-tags-1
     * test 2/20
     */
    public function testFormHasCorrectElements() {
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTags($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_TAGS)
                ->with(self::$SELECTOR_STATS_TAGS, function(Browser $stats_tags) use ($accounts) {
                    $stats_tags
                        ->assertVisible(self::$SELECTOR_STATS_FORM_TAGS)
                        ->with(self::$SELECTOR_STATS_FORM_TAGS, function(Browser $form) use ($accounts) {
                            // account/account-type selector
                            $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                            // tags-input
                            $this->assertDefaultStateOfTagsInput($form);

                            // date range
                            $this->assertDefaultStateDateRange($form);

                            // button
                            $form
                                ->assertVisible(self::$SELECTOR_BUTTON_GENERATE)
                                ->assertSeeIn(self::$SELECTOR_BUTTON_GENERATE, self::$LABEL_GENERATE_CHART_BUTTON);
                            $this->assertElementTextColor($form, self::$SELECTOR_BUTTON_GENERATE, $this->tailwindColors->white());
                            $this->assertElementBackgroundColor($form, self::$SELECTOR_BUTTON_GENERATE, $this->tailwindColors->blue(600));
                        });
                });
        });
    }

    /**
     * @group stats-tags-1
     * test 3/20
     */
    public function testDefaultDataResultsArea() {
        $this->browse(function(Browser $browser) {
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTags($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_TAGS)
                ->with(self::$SELECTOR_STATS_TAGS, function(Browser $stats_tags) {
                    $stats_tags
                        ->assertVisible(self::$SELECTOR_STATS_RESULTS_TAGS)
                        ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_TAGS, self::$LABEL_NO_STATS_DATA);
                });
        });
    }

    /**
     * @group stats-tags-1
     * test 4/20
     */
    public function testGeneratingATagsChartWontCauseSummaryTablesToBecomeVisible() {
        $this->generatingADifferentChartWontCauseSummaryTablesToBecomeVisible(
            self::$SELECTOR_STATS_SIDE_PANEL_OPTION_TAGS,
            self::$SELECTOR_STATS_FORM_TAGS,
            self::$SELECTOR_STATS_RESULTS_TAGS
        );
    }

    public function providerTestGenerateTagsChart(): array {
        return [
            //[$datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $tag_count, $include_transfers]
            // test 1/20
            'defaults account/account-type & tags & date-picker values' => [null, null, false, false, false, 0, false],
            // test 2/20
            'defaults account/account-type & tags & date-picker values & include transfers checkbox button clicked' => [null, null, false, false, false, 0, true],
            // test 3/20
            'date-picker 3 months prior start to present & default tags & default account/account-type'=>[$this->three_months_prior_start, $this->today, false, false, false, 0, false],
            // test 4/20
            'date-picker 3 months prior start to present & default tags & default account/account-type & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, false, false, false, 0, true],
            // test 5/20
            'date-picker 3 months prior start to present & default tags & random account'=>[$this->three_months_prior_start, $this->today, false, true, false, 0, false],
            // test 6/20
            'date-picker 3 months prior start to present & default tags & random account & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, false, true, false, 0, true],
            // test 7/20
            'date-picker 3 months prior start to present & default tags & random account-type'=>[$this->three_months_prior_start, $this->today, true, true, false, 0, false],
            // test 8/20
            'date-picker 3 months prior start to present & default tags & random account-type & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, true, true, false, 0, true],
            // test 9/20
            'date-picker 3 months prior start to present & default tags & random disabled account'=>[$this->three_months_prior_start, $this->today, false, true, true, 0, false],
            // test 10/20
            'date-picker 3 months prior start to present & default tags & random disabled account & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, false, true, true, 0, true],
            // test 11/20
            'date-picker 3 months prior start to present & default tags & random disabled account-type'=>[$this->three_months_prior_start, $this->today, true, true, true, 0, false],
            // test 12/20
            'date-picker 3 months prior start to present & default tags & random disabled account-type & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, true, true, true, 0, true],
            // test 13/20
            'date-picker 3 months prior start to present & random tag & default account/account-type'=>[$this->three_months_prior_start, $this->today, false, false, false, 1, false],
            // test 14/20
            'date-picker 3 months prior start to present & random tag & default account/account-type & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, false, false, false, 1, true],
            // test 15/20
            'date-picker 3 months prior start to present & random tag & random account'=>[$this->three_months_prior_start, $this->today, false, true, false, 1, false],
            // test 16/20
            'date-picker 3 months prior start to present & random tag & random account & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, false, true, false, 1, true],
            // test 17/20
            'date-picker 3 months prior start to present & random tag & random account-type'=>[$this->three_months_prior_start, $this->today, true, true, false, 1, false],
            // test 18/20
            'date-picker 3 months prior start to present & random tag & random account-type & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, true, true, false, 1, true],
            // test 19/20
            'date-picker 3 months prior start to present & random tag & random disabled account'=>[$this->three_months_prior_start, $this->today, false, true, true, 1, false],
            // test 20/20
            'date-picker 3 months prior start to present & random tag & random disabled account & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, false, true, true, 1, true],
            // test 21/20
            'date-picker 3 months prior start to present & random tag & random disabled account-type'=>[$this->three_months_prior_start, $this->today, true, true, true, 1, false],
            // test 22/20
            'date-picker 3 months prior start to present & random tag & random disabled account-type & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, true, true, true, 1, true],
            // test 23/20
            'date-picker 3 months prior start to present & random tags & default account/account-type'=>[$this->three_months_prior_start, $this->today, false, false, false, 2, false],
            // test 24/20
            'date-picker 3 months prior start to present & random tags & default account/account-type & include transfers checkbox button clicked'=>[$this->three_months_prior_start, $this->today, false, false, false, 2, true],
            // test 25/20
            'defaults account/account-type & tags; date-picker today ONLY'=>[$this->today, $this->today, false, false, false, 0, false],
            // test 26/20
            'defaults account/account-type & tags; date-picker today ONLY; include transfers'=>[$this->today, $this->today, false, false, false, 0, true],
        ];
    }

    /**
     * @dataProvider providerTestGenerateTagsChart
     *
     * @group stats-tags-2
     * test (see provider)/20
     */
    public function testGenerateTagsChart(?string $datepicker_start, ?string $datepicker_end, bool $is_switch_toggled, bool $is_random_selector_value, bool $are_disabled_select_options_available, int $tag_count, bool $include_transfers) {
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());
        $tags = collect($this->getApiTags());

        $this->browse(function(Browser $browser) use ($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types, $tag_count, $tags, $include_transfers) {
            $filter_data = [];

            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTags($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_TAGS)
                ->with(self::$SELECTOR_STATS_FORM_TAGS, function(Browser $form) use ($is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types, &$filter_data, $tag_count, $tags, $datepicker_start, $datepicker_end) {
                    if ($are_disabled_select_options_available) {
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }

                    if ($is_switch_toggled) {
                        // switch to account-types
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = $is_random_selector_value ? $account_types->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : null;
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = $is_random_selector_value ? $accounts->where('active', !$are_disabled_select_options_available)->pluck('id')->random() : null;
                    }
                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_switch_toggled, $account_or_account_type_id);

                    $form_tags = $tags->chunk($tag_count)->first();
                    if (!is_null($form_tags)) {
                        foreach ($form_tags as $tag) {
                            $this->fillTagsInputUsingAutocomplete($form, $tag['name']);
                            $this->assertTagInInput($form, $tag['name']);
                        }
                    }
                    $filter_data = $this->generateFilterArrayElementTags($filter_data, $form_tags);

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

                    $this->generateEntryFromFilterData($filter_data, $this->getName(true));
                    $this->createEntryWithAllTags($is_switch_toggled, $account_or_account_type_id, $account_types, $tags);
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $this->waitForLoadingToStop($browser);
            $entries = $this->getBatchedFilteredEntries($filter_data);
            $entries = $this->filterTransferEntries($entries, $include_transfers);

            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_TAGS, self::$LABEL_NO_STATS_DATA)
                ->with(self::$SELECTOR_STATS_RESULTS_TAGS, function(Browser $stats_results_area) use ($include_transfers) {
                    $this->assertIncludeTransfersButtonDefaultState($stats_results_area);
                    if ($include_transfers) {
                        $this->clickIncludeTransfersCheckboxButton($stats_results_area);
                        $this->assertIncludesTransfersCheckboxButtonStateActive($stats_results_area);
                    }

                    $stats_results_area->assertVisible(self::$SELECTOR_CHART_TAGS);
                })
                ->assertVue(self::$VUE_KEY_STANDARDISEDATA, $this->standardiseData($entries, $tags), self::$SELECTOR_STATS_TAGS);
        });
    }

    /**
     * @param Collection $entries
     * @param Collection $tags
     */
    private function standardiseData($entries, $tags): array {
        $standardised_chart_data = [];

        foreach ($entries as $entry) {
            if (count($entry['tags']) === 0) {
                $entry['tags'][] = 0;
            }

            foreach ($entry['tags'] as $tag) {
                $key = ($tag === 0) ? 'untagged' : $tags->where('id', $tag)->pluck('name')->first();
                if (!isset($standardised_chart_data[$key])) {
                    $standardised_chart_data[$key] = ['x' => $key, 'y' => 0];
                }

                if ($entry['expense']) {
                    $standardised_chart_data[$key]['y'] -= $entry['entry_value'];
                } else {
                    $standardised_chart_data[$key]['y'] += $entry['entry_value'];
                }
                $standardised_chart_data[$key]['y'] = round($standardised_chart_data[$key]['y'], 2);
            }
        }
        $x_col = array_column($standardised_chart_data, 'x');
        array_multisort($x_col, SORT_ASC, $standardised_chart_data);
        return array_values($standardised_chart_data);
    }

    /**
     * @param Collection $account_types
     * @param Collection $tags
     */
    private function createEntryWithAllTags(bool $is_account_type_rather_than_account_toggled, ?int $account_or_account_type_id, $account_types, $tags) {
        if (!empty($account_or_account_type_id)) {
            if ($is_account_type_rather_than_account_toggled) {
                $account_type_id = $account_or_account_type_id;
            } else {
                $account_type_id = $account_types->where('account_id', $account_or_account_type_id)->pluck('id')->first();
            }
        } else {
            $account_type_id = $account_types->pluck('id')->random();
        }

        $entry_with_tags = Entry::factory()->create(['memo'=>$this->getName(true).' - ALL TAGS', 'account_type_id'=>$account_type_id, 'entry_date'=>date('Y-m-d')]);
        foreach ($tags->pluck('id')->all() as $tag_id) {
            $entry_with_tags->tags()->attach($tag_id);
        }
    }

}
