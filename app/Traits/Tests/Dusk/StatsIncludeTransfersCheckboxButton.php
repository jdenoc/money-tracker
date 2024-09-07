<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\AssertElementColor;
use Exception;
use Laravel\Dusk\Browser;

trait StatsIncludeTransfersCheckboxButton {
    use AssertElementColor;

    // variables
    private static $LABEL_STATS_INCLUDES_TRANSFER = "Include Transfers";
    protected $include_transfers_chart_name = '';

    private function hasIncludeTransfersChartNameBeenSet() {
        if (!$this->include_transfers_chart_name) {
            throw new Exception("variable \$include_transfers_chart_name has not been set");
        }
    }

    protected function getIncludeTransfersId(): string {
        $this->hasIncludeTransfersChartNameBeenSet();
        $element_selector_template = "#%s-include-transfers";
        return sprintf($element_selector_template, $this->include_transfers_chart_name);
    }

    protected function getIncludeTransferLabelSelector(): string {
        $element_selector_template = "label[for='%s']";
        return sprintf($element_selector_template, ltrim($this->getIncludeTransfersId(), '#'));
    }

    protected function assertIncludeTransfersCheckboxButtonNotVisible(Browser $browser) {
        $browser->assertMissing($this->getIncludeTransferLabelSelector());
    }

    protected function assertIncludeTransfersButtonDefaultState(Browser $browser) {
        $browser
            ->assertMissing($this->getIncludeTransfersId())
            ->assertVisible($this->getIncludeTransferLabelSelector())
            ->assertSeeIn($this->getIncludeTransferLabelSelector(), self::$LABEL_STATS_INCLUDES_TRANSFER);
        $this->assertIncludesTransfersCheckboxButtonStateInactive($browser);
    }

    protected function clickIncludeTransfersCheckboxButton(Browser $browser) {
        $browser->click($this->getIncludeTransferLabelSelector());
    }

    protected function assertIncludesTransfersCheckboxButtonStateActive(Browser $browser) {
        $browser
            ->assertVisible($this->getIncludeTransferLabelSelector())
            ->assertChecked($this->getIncludeTransfersId());
        $this->assertElementBackgroundColor($browser, $this->getIncludeTransferLabelSelector(), $this->tailwindColors->blue(500));
        $this->assertElementTextColor($browser, $this->getIncludeTransferLabelSelector(), $this->tailwindColors->white());
    }

    protected function assertIncludesTransfersCheckboxButtonStateInactive(Browser $browser) {
        $browser
            ->assertVisible($this->getIncludeTransferLabelSelector())
            ->assertNotChecked($this->getIncludeTransfersId());
        $this->assertElementBackgroundColor($browser, $this->getIncludeTransferLabelSelector(), $this->tailwindColors->gray(50));
        $this->assertElementTextColor($browser, $this->getIncludeTransferLabelSelector(), $this->tailwindColors->black());
    }

}
