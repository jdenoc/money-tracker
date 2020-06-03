<?php

namespace Tests\Browser;

use App\Entry;
use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BatchFilterEntries as DuskTraitBatchFilterEntries;
use App\Traits\Tests\Dusk\BulmaDatePicker as DuskTraitBulmaDatePicker;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
use Illuminate\Support\Collection;
use Tests\Browser\Pages\StatsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Laravel\Dusk\Browser;
use Throwable;

/**
 * Class StatsTagsTest
 *
 * @package Tests\Browser
 *
 * @group stats
 * @group stats-tags
 */
class StatsTagsTest extends DuskTestCase {

    use DuskTraitLoading;
    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBatchFilterEntries;
    use DuskTraitBulmaDatePicker;
    use DuskTraitTagsInput;
    use DuskTraitStatsSidePanel;

    private static $SELECTOR_STATS_TAGS = "#stats-tags";
    private static $SELECTOR_STATS_FORM_TAGS = "#stats-form-tags";
    private static $SELECTOR_BUTTON_GENERATE = '.generate-stats';
    private static $SELECTOR_STATS_RESULTS_AREA = '.stats-results-tags';
    private static $SELECTOR_CHART_TAGS = 'canvas#bar-chart';

    private static $LABEL_GENERATE_CHART_BUTTON = 'Generate Chart';
    private static $LABEL_NO_STATS_DATA = 'No data available';

    private static $VUE_KEY_STANDARDISEDATA = "standardiseData";

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->_account_or_account_type_toggling_selector_label_id = 'tags-chart';
    }

    /**
     * @throws Throwable
     *
     * @group stats-tags-1
     * test 1/25
     */
    public function testSelectTagsSidebarOption(){
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
     * @throws Throwable
     *
     * @group stats-tags-1
     * test 2/25
     */
    public function testFormHasCorrectElements(){
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts){
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTags($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_TAGS)
                ->with(self::$SELECTOR_STATS_TAGS, function(Browser $stats_tags) use ($accounts){
                    $stats_tags
                        ->assertVisible(self::$SELECTOR_STATS_FORM_TAGS)
                        ->with(self::$SELECTOR_STATS_FORM_TAGS, function(Browser $form) use ($accounts){
                            // account/account-type selector
                            $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

                            // tags-input
                            $this->assertDefaultStateOfTagsInput($form);

                            // bulma date-picker
                            $this->assertDefaultStateBulmaDatePicker($form);

                            // button
                            $form
                                ->assertVisible(self::$SELECTOR_BUTTON_GENERATE)
                                ->assertSeeIn(self::$SELECTOR_BUTTON_GENERATE, self::$LABEL_GENERATE_CHART_BUTTON);
                            $button_classes = $form->attribute(self::$SELECTOR_BUTTON_GENERATE, 'class');
                            $this->assertContains('is-primary', $button_classes);
                        });
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-tags-1
     * test 3/25
     */
    public function testDefaultDataResultsArea(){
        $this->browse(function(Browser $browser){
            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTags($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_TAGS)
                ->with(self::$SELECTOR_STATS_TAGS, function(Browser $stats_tags){
                    $stats_tags
                        ->assertVisible(self::$SELECTOR_STATS_RESULTS_AREA)
                        ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA);
                });
        });
    }

    public function providerTestGenerateTagsChart(){
        $previous_year_start = date("Y-01-01", strtotime('-1 year'));
        $today = date("Y-m-d");
        return [
            //[$datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $tag_count]
            // defaults account/account-type & tags & date-picker values
            [null, null, false, false, false, 0],   // test 3/25
            // date-picker previous year start to present & default tags & default account/account-type
            [$previous_year_start, $today, false, false, false, 0], // test 4/25
            // date-picker previous year start to present & default tags & random account
            [$previous_year_start, $today, false, true, false, 0],  // test 5/25
            // date-picker previous year start to present & default tags & random account-type
            [$previous_year_start, $today, true, true, false, 0],   // test 6/25
            // date-picker previous year start to present & default tags & random disabled account
            [$previous_year_start, $today, false, true, true, 0],   // test 7/25
            // date-picker previous year start to present & default tags & random disabled account-type
            [$previous_year_start, $today, true, true, true, 0],    // test 8/25
            // date-picker previous year start to present & random tag & default account/account-type
            [$previous_year_start, $today, false, false, false, 1], // test 9/25
            // date-picker previous year start to present & random tag & random account
            [$previous_year_start, $today, false, true, false, 1],  // test 10/25
            // date-picker previous year start to present & random tag & random account-type
            [$previous_year_start, $today, true, true, false, 1],   // test 11/25
            // date-picker previous year start to present & random tag & random disabled account
            [$previous_year_start, $today, false, true, true, 1],   // test 12/25
            // date-picker previous year start to present & random tag & random disabled account-type
            [$previous_year_start, $today, true, true, true, 1],    // test 13/25
            // date-picker previous year start to present & random tags & default account/account-type
            [$previous_year_start, $today, false, false, false, 2]  // test 14/25
        ];
    }

    /**
     * @dataProvider providerTestGenerateTagsChart
     *
     * @param string|null $datepicker_start
     * @param string|null $datepicker_end
     * @param bool $is_switch_toggled
     * @param bool $is_random_selector_value
     * @param bool $are_disabled_select_options_available
     * @param int $tag_count
     *
     * @throws Throwable
     *
     * @group stats-tags-1
     * test (see provider)/25
     */
    public function testGenerateTagsChart($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $tag_count){
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());
        $tags = collect($this->getApiTags());

        $this->browse(function(Browser $browser) use ($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types, $tag_count, $tags){
            $filter_data = [];

            $browser->visit(new StatsPage());
            $this->clickStatsSidePanelOptionTags($browser);

            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_TAGS)
                ->with(self::$SELECTOR_STATS_FORM_TAGS, function(Browser $form) use ($is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $accounts, $account_types, &$filter_data, $tag_count, $tags, $datepicker_start, $datepicker_end){
                    if($are_disabled_select_options_available){
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }

                    if($is_switch_toggled){
                        // switch to account-types
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = $is_random_selector_value ? $account_types->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    } else {
                        // stay with accounts
                        $account_or_account_type_id = $is_random_selector_value ? $accounts->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    }
                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);
                    $filter_data = $this->generateFilterArrayElementAccountOrAccountypeId($filter_data, $is_switch_toggled, $account_or_account_type_id);

                    $form_tags = $tags->chunk($tag_count)->first();
                    if(!is_null($form_tags)){
                        foreach($form_tags as $tag){
                            $this->fillTagsInputUsingAutocomplete($form, $tag['name']);
                            $this->assertTagInInput($form, $tag['name']);
                        }
                    }
                    $filter_data = $this->generateFilterArrayElementTags($filter_data, $form_tags);

                    if(!is_null($datepicker_start) && !is_null($datepicker_end)){
                        $this->setDateRange($form, $datepicker_start, $datepicker_end);
                    } else {
                        $datepicker_start = date('Y-m-01');
                        $datepicker_end = date('Y-m-t');
                    }
                    $filter_data = $this->generateFilterArrayElementDatepicker($filter_data, $datepicker_start, $datepicker_end);

                    $this->createEntryWithAllTags($is_switch_toggled, $account_or_account_type_id, $account_types, $tags);
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $entries = $this->getBatchedFilteredEntries($filter_data);

            $this->waitForLoadingToStop($browser);
            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA)
                ->with(self::$SELECTOR_STATS_RESULTS_AREA, function(Browser $stats_results_area){
                    $stats_results_area->assertVisible(self::$SELECTOR_CHART_TAGS);
                })
                ->assertVue(self::$VUE_KEY_STANDARDISEDATA, $this->standardiseData($entries, $tags), self::$SELECTOR_STATS_TAGS);
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
            if(count($entry['tags']) === 0){
                $entry['tags'][] = 0;
            }

            foreach($entry['tags'] as $tag){
                $key = ($tag === 0) ? 'untagged' : $tags->where('id', $tag)->pluck('name')->first();
                if(!isset($standardised_chart_data[$key])){
                    $standardised_chart_data[$key] = ['x'=>$key, 'y'=>0];
                }

                if($entry['expense']){
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
     * Database seeder doesn't assign tags to disabled entries.
     * It's a waste of resources to do that for every test when most tests don't need that kind of data.
     * So instead for these tests, we'll create a disabled with all the tags
     *
     * @param bool $is_account_type_rather_than_account_toggled
     * @param int $account_or_account_type_id
     * @param Collection $account_types
     * @param Collection $tags
     */
    private function createEntryWithAllTags($is_account_type_rather_than_account_toggled, $account_or_account_type_id, $account_types, $tags){
        if(!empty($account_or_account_type_id)){
            if($is_account_type_rather_than_account_toggled){
                $account_type_id = $account_or_account_type_id;
            } else {
                $account_type_id = $account_types->where('account_id', $account_or_account_type_id)->pluck('id')->first();
            }
        } else {
            $account_type_id = $account_types->pluck('id')->random();
        }

        $entry_with_tags = factory(Entry::class)->create(['account_type_id'=>$account_type_id, 'disabled'=>false, 'entry_date'=>date('Y-m-d')]);
        foreach($tags->pluck('id')->all() as $tag_id){
            $entry_with_tags->tags()->attach($tag_id);
        }
    }

}
