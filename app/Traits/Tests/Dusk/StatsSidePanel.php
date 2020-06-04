<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait StatsSidePanel {

    private static $SELECTOR_STATS_SIDE_PANEL = ".panel";
    private static $SELECTOR_STATS_SIDE_PANEL_HEADING = '.panel-heading:first-child';
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_SUMMARY = ".panel-heading+.panel-block";
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_TRENDING = ".panel-block:nth-child(3)";
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_DISTRIBUTION = ".panel-block:nth-child(4)";
    private static $SELECTOR_STATS_SIDE_PANEL_OPTION_TAGS = ".panel-block:nth-child(5)";
    private static $SELECTOR_STATS_SIDE_PANEL_ACTIVE_OPTION = ".panel-block.is-active";

    private static $LABEL_STATS_SIDE_PANEL_HEADING = "Stats";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY = "Summary";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_TRENDING = "Trending";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_DISTRIBUTION = "Distribution";
    private static $LABEL_STATS_SIDE_PANEL_OPTION_TAGS = "Tags";

    public function assertStatsSidePanelHeading(Browser $browser){
        $browser
            ->with(self::$SELECTOR_STATS_SIDE_PANEL, function(Browser $side_panel){
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
            ->with(self::$SELECTOR_STATS_SIDE_PANEL, function(Browser $side_panel) use ($option_selector){
                $side_panel->click($option_selector);
            });
    }

    public function assertStatsSidePanelOptionIsActive(Browser $browser, $label){
        if(!in_array($label, $this->statsSidePanelOptionLabels())){
            throw new \InvalidArgumentException("Label '".$label."' provided is not a valid stats side panel option");
        }
        
        $browser
            ->with(self::$SELECTOR_STATS_SIDE_PANEL, function(Browser $side_panel) use ($label){
                $side_panel->assertSeeIn(self::$SELECTOR_STATS_SIDE_PANEL_ACTIVE_OPTION, $label);
            });
    }

    /**
     * @return array
     */
    private function statsSidePanelOptionLabels(){
        return [
            self::$LABEL_STATS_SIDE_PANEL_OPTION_SUMMARY,
            self::$LABEL_STATS_SIDE_PANEL_OPTION_TRENDING,
            self::$LABEL_STATS_SIDE_PANEL_OPTION_DISTRIBUTION,
            self::$LABEL_STATS_SIDE_PANEL_OPTION_TAGS
        ];
    }

}
