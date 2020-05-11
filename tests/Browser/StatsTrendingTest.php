<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\AccountOrAccountTypeTogglingSelector as DuskTraitAccountOrAccountTypeTogglingSelector;
use App\Traits\Tests\Dusk\BulmaDatePicker as DuskTraitBulmaDatePicker;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StatsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Throwable;

/**
 * Class StatsTrendingTest
 *
 * @package Tests\Browser
 *
 * @group stats
 * @group stats-trending
 */
class StatsTrendingTest extends DuskTestCase {

    use DuskTraitAccountOrAccountTypeTogglingSelector;
    use DuskTraitBulmaDatePicker;
    use DuskTraitLoading;

    private static $SELECTOR_STATS_FORM_TRENDING = "#stats-form-trending";
    private static $SELECTOR_BUTTON_GENERATE = '.generate-stats';
    private static $SELECTOR_STATS_RESULTS_AREA = '.stats-results-trending';
    private static $SELECTOR_SIDE_PANEL = '.panel';
    private static $SELECTOR_SIDE_PANEL_OPTION_TRENDING = '.panel-block:nth-child(3)';
    private static $SELECTOR_CHART_TRENDING = 'canvas#line-chart';

    private static $LABEL_OPTION_TRENDING = "Trending";
    private static $LABEL_GENERATE_CHART_BUTTON = "Generate Chart";
    private static $LABEL_NO_STATS_DATA = 'No data available';

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->_id_label = 'trending-chart';
    }

    /**
     * @throws Throwable
     *
     * @group stats-trending-1
     * test 1/25
     */
    public function testSelectTrendingSidebarOption(){
        $this->browse(function(Browser $browser) {
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_SIDE_PANEL)
                ->within(self::$SELECTOR_SIDE_PANEL, function(Browser $sidepanel){
                    $class_is_active = 'is-active';
                    $classes = $sidepanel->attribute(self::$SELECTOR_SIDE_PANEL_OPTION_TRENDING, 'class');
                    $this->assertNotContains($class_is_active, $classes);
                    
                    $sidepanel
                        ->assertSeeIn(self::$SELECTOR_SIDE_PANEL_OPTION_TRENDING, self::$LABEL_OPTION_TRENDING)
                        ->click(self::$SELECTOR_SIDE_PANEL_OPTION_TRENDING);
                    $classes = $sidepanel->attribute(self::$SELECTOR_SIDE_PANEL_OPTION_TRENDING, 'class');
                    $this->assertContains($class_is_active, $classes);
                });
        });
    }

    /**
     * @throws Throwable
     *
     * @group stats-trending-1
     * test 2/25
     */
    public function testFormHasCorrectElements(){
        $accounts = $this->getApiAccounts();

        $this->browse(function(Browser $browser) use ($accounts){
            $browser
                ->visit(new StatsPage())
                ->within(self::$SELECTOR_SIDE_PANEL, function(Browser $sidepanel){
                    $sidepanel->click(self::$SELECTOR_SIDE_PANEL_OPTION_TRENDING);
                })

                ->assertVisible(self::$SELECTOR_STATS_FORM_TRENDING)
                ->with(self::$SELECTOR_STATS_FORM_TRENDING, function(Browser $form) use ($accounts){
                    // account/account-type selector
                    $this->assertDefaultStateOfAccountOrAccountTypeTogglingSelectorComponent($form, $accounts);

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
     * @group stats-trending-1
     * test 3/25
     */
    public function testDefaultDataResultsArea(){
        $this->browse(function(Browser $browser){
            $browser
                ->visit(new StatsPage())
                ->within(self::$SELECTOR_SIDE_PANEL, function(Browser $sidepanel){
                    $sidepanel->click(self::$SELECTOR_SIDE_PANEL_OPTION_TRENDING);
                })
                ->assertVisible(self::$SELECTOR_STATS_RESULTS_AREA)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_AREA, self::$LABEL_NO_STATS_DATA);
        });
    }

    public function providerTestGenerateTrendingChart(){
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
     * @dataProvider providerTestGenerateTrendingChart
     *
     * @param $datepicker_start
     * @param $datepicker_end
     * @param $is_switch_toggled
     * @param $is_random_selector_value
     * @param $are_disabled_select_options_available
     *
     * @throws Throwable
     *
     * @group stats-trending-1
     * test (see provider)/25
     */
    public function testGenerateTrendingChart($datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available){
        $accounts = collect($this->getApiAccounts());
        $account_types = collect($this->getApiAccountTypes());

        $this->browse(function (Browser $browser) use ($accounts, $account_types, $datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available){
            $browser
                ->visit(new StatsPage())
                ->with(self::$SELECTOR_SIDE_PANEL, function(Browser $side_panel){
                    $side_panel->click(self::$SELECTOR_SIDE_PANEL_OPTION_TRENDING);
                })

                ->assertVisible(self::$SELECTOR_STATS_FORM_TRENDING)
                ->with(self::$SELECTOR_STATS_FORM_TRENDING, function(Browser $form) use ($accounts, $account_types, $datepicker_start, $datepicker_end, $is_switch_toggled, $is_random_selector_value, $are_disabled_select_options_available){
                    if($are_disabled_select_options_available){
                        $this->toggleShowDisabledAccountOrAccountTypeCheckbox($form);
                    }
                    $account_or_account_type_id = null;
                    if($is_switch_toggled){
                        $this->toggleAccountOrAccountTypeSwitch($form);
                        $account_or_account_type_id = ($is_random_selector_value) ? $account_types->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    } else {
                        $account_or_account_type_id = ($is_random_selector_value) ? $accounts->where('disabled', $are_disabled_select_options_available)->pluck('id')->random() : '';
                    }

                    $this->selectAccountOrAccountTypeValue($form, $account_or_account_type_id);

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
                        $stats_results->assertVisible(self::$SELECTOR_CHART_TRENDING);
                    });
        });
    }

}
