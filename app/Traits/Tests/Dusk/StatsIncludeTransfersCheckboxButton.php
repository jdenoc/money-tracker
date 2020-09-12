<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait StatsIncludeTransfersCheckboxButton {

    protected static $SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS = '.is-checkradio.is-info.is-small.is-block+label';
    protected static $SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS_CHECKED = '.is-checkradio.is-info.is-small.is-block:checked+label';

    protected static $LABEL_CHECKBOX_INCLUDES_TRANSFER = "Include Transfers";

    /**
     * @param Browser $browser
     */
    protected function assertIncludeTransfersCheckboxButtonNotVisible(Browser $browser){
        $browser->assertMissing(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS);
    }

    /**
     * @param Browser $browser
     */
    protected function assertIncludeTransfersCheckboxButtonDefaultState(Browser $browser){
        $browser
            ->assertVisible(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS)
            ->assertSeeIn(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS, self::$LABEL_CHECKBOX_INCLUDES_TRANSFER);
        $this->assertIncludesTransfersCheckboxButtonStateInactive($browser);
    }

    /**
     * @param Browser $browser
     */
    protected function clickIncludeTransfersCheckboxButton(Browser $browser){
        $browser->click(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS);
    }

    /**
     * @param Browser $browser
     */
    protected function assertIncludesTransfersCheckboxButtonStateActive(Browser $browser){
        $browser
            ->assertVisible(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS)
            ->assertVisible(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS_CHECKED);
    }

    /**
     * @param Browser $browser
     */
    protected function assertIncludesTransfersCheckboxButtonStateInactive(Browser $browser){
        $browser
            ->assertVisible(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS)
            ->assertMissing(self::$SELECTOR_STATS_CHECKBOX_INCLUDE_TRANSFERS_CHECKED);
    }

}