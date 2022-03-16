<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait StatsSidePanel {

    private static $SELECTOR_STATS_SIDE_PANEL = "#stats-nav";
    private static $SELECTOR_STATS_SIDE_PANEL_HEADING = '#stats-panel-header';
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_SUMMARY = "li.stats-nav-option:nth-child(1)";
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_TRENDING = "li.stats-nav-option:nth-child(2)";
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_DISTRIBUTION = "li.stats-nav-option:nth-child(3)";
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_TAGS = "li.stats-nav-option:nth-child(4)";
    private static $SELECTOR_STATS_SIDE_PANEL_ACTIVE_OPTION = "li.stats-nav-option.is-active";

    private static $LABEL_STATS_SIDE_PANEL_HEADING = "Stats";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY = "Summary";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_TRENDING = "Trending";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_DISTRIBUTION = "Distribution";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_TAGS = "Tags";

    public function assertStatsSidePanelHeading(Browser $browser){
        $browser
            ->within(self::$SELECTOR_STATS_SIDE_PANEL, function(Browser $side_panel){
                $side_panel
                    ->assertVisible(self::$SELECTOR_STATS_SIDE_PANEL_HEADING)
                    ->assertSeeIn(self::$SELECTOR_STATS_SIDE_PANEL_HEADING, self::$LABEL_STATS_SIDE_PANEL_HEADING);
            });
    }

    public function clickStatsSidePanelOptionSummary(Browser $browser){
        $this->clickStatsSidePanelOption($browser, self::$SELECTOR_STATS_SIDE_PANEL_OPTION_SUMMARY);
    }

    public function clickStatsSidePanelOptionTrending(Browser $browser){
        $this->clickStatsSidePanelOption($browser, self::$SELECTOR_STATS_SIDE_PANEL_OPTION_TRENDING);
    }

    public function clickStatsSidePanelOptionDistribution(Browser $browser){
        $this->clickStatsSidePanelOption($browser, self::$SELECTOR_STATS_SIDE_PANEL_OPTION_DISTRIBUTION);
    }

    public function clickStatsSidePanelOptionTags(Browser $browser){
        $this->clickStatsSidePanelOption($browser, self::$SELECTOR_STATS_SIDE_PANEL_OPTION_TAGS);
    }

    private function clickStatsSidePanelOption(Browser $browser, $option_selector){
        $browser
            ->within(self::$SELECTOR_STATS_SIDE_PANEL, function(Browser $side_panel) use ($option_selector){
                $side_panel->click($option_selector);
            });
    }

    public function assertStatsSidePanelOptionIsActive(Browser $browser, string $label){
        if(!in_array($label, $this->statsSidePanelOptionLabels())){
            throw new \InvalidArgumentException("Label '".$label."' provided is not a valid stats side panel option");
        }
        
        $browser
            ->within(self::$SELECTOR_STATS_SIDE_PANEL, function(Browser $side_panel) use ($label){
                $side_panel->assertSeeIn(self::$SELECTOR_STATS_SIDE_PANEL_ACTIVE_OPTION, $label);
            });
    }

    /**
     * @return array
     */
    private function statsSidePanelOptionLabels():array{
        return [
            self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY,
            self::$LABEL_STATS_SIDE_PANEL_OPTION_TRENDING,
            self::$LABEL_STATS_SIDE_PANEL_OPTION_DISTRIBUTION,
            self::$LABEL_STATS_SIDE_PANEL_OPTION_TAGS
        ];
    }

}
