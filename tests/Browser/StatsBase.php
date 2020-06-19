<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\BulmaDatePicker as DuskTraitBulmaDatePicker;
use App\Traits\Tests\Dusk\Loading as DuskTraitLoading;
use App\Traits\Tests\Dusk\StatsSidePanel as DuskTraitStatsSidePanel;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\StatsPage;
use Tests\DuskWithMigrationsTestCase as DuskTestCase;
use Throwable;

class StatsBase extends DuskTestCase {

    use DuskTraitBulmaDatePicker;
    use DuskTraitLoading;
    use DuskTraitStatsSidePanel;

    protected static $SELECTOR_STATS_FORM_SUMMARY = "#stats-form-summary";
    protected static $SELECTOR_STATS_FORM_TRENDING = '#stats-form-trending';
    protected static $SELECTOR_STATS_FORM_DISTRIBUTION = '#stats-form-distribution';
    protected static $SELECTOR_STATS_FORM_TAGS = '#stats-form-tags';

    protected static $SELECTOR_STATS_RESULTS_SUMMARY = '.stats-results-summary';
    protected static $SELECTOR_STATS_RESULTS_TRENDING = '.stats-results-trending';
    protected static $SELECTOR_STATS_RESULTS_DISTRIBUTION = '.stats-results-distribution';
    protected static $SELECTOR_STATS_RESULTS_TAGS = '.stats-results-tags';

    protected static $SELECTOR_BUTTON_GENERATE = '.generate-stats';

    protected static $LABEL_GENERATE_CHART_BUTTON = "Generate Chart";
    protected static $LABEL_NO_STATS_DATA = 'No data available';

    protected $today = '';
    protected $previous_year_start = '';
    protected $month_start = '';
    protected $month_end = '';

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->today = date("Y-m-d");
        $this->previous_year_start = date("Y-01-01", strtotime('-1 year'));
        $this->month_start = date('Y-m-01');
        $this->month_end = date("Y-m-t");
    }

    /**
     * @param string $side_panel_selector
     * @param string $stats_form_selector
     * @param string $stats_results_selector
     *
     * @throws Throwable
     */
    protected function generatingADifferentChartWontCauseSummaryTablesToBecomeVisible($side_panel_selector, $stats_form_selector, $stats_results_selector){
        $this->browse(function (Browser $browser) use ($side_panel_selector, $stats_form_selector, $stats_results_selector){
            $browser
                ->visit(new StatsPage())
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY);

            $this->clickStatsSidePanelOption($browser, $side_panel_selector);
            $browser
                ->assertVisible($stats_form_selector)
                ->with($stats_form_selector, function(Browser $form){
                    $this->setDateRange($form, $this->previous_year_start, $this->today);
                    $form->click(self::$SELECTOR_BUTTON_GENERATE);
                });
            $this->waitForLoadingToStop($browser);
            $browser->assertDontSeeIn($stats_results_selector, self::$LABEL_NO_STATS_DATA);

            $this->clickStatsSidePanelOptionSummary($browser);
            $this->assertStatsSidePanelOptionIsActive($browser, self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY);
            $browser
                ->assertVisible(self::$SELECTOR_STATS_FORM_SUMMARY)
                ->assertSeeIn(self::$SELECTOR_STATS_RESULTS_SUMMARY, self::$LABEL_NO_STATS_DATA);
        });
    }

}