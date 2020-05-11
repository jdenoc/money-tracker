<?php

namespace Tests\Browser;

use App\Entry;
use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BulmaDatePicker as DuskTraitBulmaDatePicker;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\TagsInput as DuskTraitTagsInput;
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
    use DuskTraitBulmaDatePicker;
    use DuskTraitTagsInput;

    private static $SELECTOR_STATS_FORM_TAGS = "#stats-form-tags";
    private static $SELECTOR_BUTTON_GENERATE = '.generate-stats';
    private static $SELECTOR_STATS_RESULTS_AREA = '.stats-results-tags';
    private static $SELECTOR_SIDE_PANEL = '.panel';
    private static $SELECTOR_SIDE_PANEL_OPTION_TAGS = '.panel-block:nth-child(4)';
    private static $SELECTOR_CHART_TAGS = 'canvas#bar-chart';

    private static $LABEL_OPTION_TAGS = "Tags";
    private static $LABEL_GENERATE_CHART_BUTTON = 'Generate Chart';
    private static $LABEL_NO_STATS_DATA = 'No data available';

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->_id_label = 'tags-chart';
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
                ->assertVisible(self::$SELECTOR_SIDE_PANEL)
                ->with(self::$SELECTOR_SIDE_PANEL, function(Browser $side_panel){
                    $class_is_active = 'is-active';
                    $classes = $side_panel->attribute(self::$SELECTOR_SIDE_PANEL_OPTION_TAGS, 'class');
                    $this->assertNotContains($class_is_active, $classes);

                    $side_panel
                        ->assertSeeIn(self::$SELECTOR_SIDE_PANEL_OPTION_TAGS, self::$LABEL_OPTION_TAGS)
                        ->click(self::$SELECTOR_SIDE_PANEL_OPTION_TAGS);
                    $classes = $side_panel->attribute(self::$SELECTOR_SIDE_PANEL_OPTION_TAGS, 'class');
                    $this->assertContains($class_is_active, $classes);
                });
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
            $browser
                ->visit(new StatsPage())
                ->with(self::$SELECTOR_SIDE_PANEL, function(Browser $side_panel){
                    $side_panel->click(self::$SELECTOR_SIDE_PANEL_OPTION_TAGS);
                })

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
    }

    /**
     * @throws Throwable
     *
     * @group stats-tags-1
     * test 3/25
     */
    public function testDefaultDataResultsArea(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->with(self::$SELECTOR_SIDE_PANEL, function(Browser $side_panel){
                    $side_panel->click(self::$SELECTOR_SIDE_PANEL_OPTION_TAGS);
                })
                ->assertVisible(self::$SELECTOR_STATS_RESULTS_AREA)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA);
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
     * @group stats-trending-1
     * test (see provider)/25
     */
    public function testGenerateTagsChart($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available, $tag_count){
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());
        $tags = collect($this->getApiTags())->chunk($tag_count)->first();

        $filter_entries = [];
        if($is_switch_toggled){
            $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
            $filter_entries['account_type']=$account_or_account_type_id;
        } else {
            $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
            $filter_entries['account']=$account_or_account_type_id;
        }

        if(!is_null($tags)){
            // make sure that a tag is associated with an entry
            if(!$is_random_selector_value){
                // Not selecting an account/account-type, no need to actually do any filtering
                $filter_entries = [];
            }
            $entries = collect($this->removeCountFromApiResponse($this->getApiEntries(0, $filter_entries)));
            $entry = $entries->random();
            $e = Entry::findOrFail($entry['id']);
            $e->tags()->attach($tags->first()['id']);
        }

        $this->browse(function (Browser $browser) use ($account_or_account_type_id, $datepicker_start, $datepicker_end, $is_switch_toggled, $are_disabled_select_options_available, $tags){
            $browser
                ->visit(new StatsPage())
                ->with(self::$SELECTOR_SIDE_PANEL, function(Browser $side_panel){
                    $side_panel->click(self::$SELECTOR_SIDE_PANEL_OPTION_TAGS);
                })

                ->assertVisible(self::$SELECTOR_STATS_FORM_TAGS)
                ->with(self::$SELECTOR_STATS_FORM_TAGS, function(Browser $form) use ($account_or_account_type_id, $datepicker_start, $datepicker_end, $is_switch_toggled, $are_disabled_select_options_available, $tags){
                    if($are_disabled_select_options_available){
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }
                    if($is_switch_toggled){
                        $this->toggleAccountOrAccountTypeSwitch($form);
                    }

                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);

                    if(!is_null($tags)){
                        foreach($tags as $tag){
                            $this->fillTagsInputUsingAutocomplete($form, $tag['name']);
                            $this->assertTagInInput($form, $tag['name']);
                        }
                    }

                    if(!is_null($datepicker_start) && !is_null($datepicker_end)){
                        $this->setDateRange($form, $datepicker_start, $datepicker_end);
                    }

                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });

            $this->waitForLoadingToStop($browser);
            $browser
                ->assertDontSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA)
                ->with(self::$SELECTOR_STATS_RESULTS_AREA, function(Browser $stats_results){
                    //  line-chart graph canvas should be visible
                    $stats_results->assertVisible(self::$SELECTOR_CHART_TAGS);
                });
        });
    }
}
