<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait Tooltip {

    private static string $SELECTOR_TOOLTIP = '.tooltip';
    private static string $TEMPLATE_SELECTOR_TOOLTIP_ID = '.tooltip#%s';

    protected function assertTooltipMissing(Browser $browser) {
        $browser->elsewhere('', function(Browser $body) {
            $body->assertMissing(self::$SELECTOR_TOOLTIP);
        });
    }

    protected function assertTooltipVisible(Browser $browser, string $tooltipId=null) {
        $tooltip_selector = $this->getTooltipElementSelector($tooltipId);
        $browser->elsewhere('', function(Browser $body) use ($tooltip_selector) {
            $body->assertVisible($tooltip_selector);
        });
    }

    protected function assertTooltipVisibleByTriggerElement(Browser $browser, string $triggerElementSelector) {
        $tooltip_id = $this->getTooltipIdFromTriggerElement($browser, $triggerElementSelector);
        $this->assertTooltipVisible($browser, $tooltip_id);
    }

    protected function assertStringInTooltipContents(Browser $browser, string $expectedStringInTooltipContents, string $tooltipId=null) {
        $tooltip_selector = $this->getTooltipElementSelector($tooltipId);
        $browser->elsewhere('', function(Browser $body) use ($tooltip_selector, $expectedStringInTooltipContents) {
            $body
                ->assertVisible($tooltip_selector)
                ->assertSeeIn($tooltip_selector, $expectedStringInTooltipContents);
        });
    }

    protected function assertStringInTooltipContentsByTriggerElement(Browser $browser, string $expectedStringInTooltipContents, string $triggerElementSelector) {
        $tooltip_id = $this->getTooltipIdFromTriggerElement($browser, $triggerElementSelector);
        $this->assertStringInTooltipContents($browser, $expectedStringInTooltipContents, $tooltip_id);
    }

    protected function assertStringNotInTooltipContents(Browser $browser, string $expectedStringNotInTooltipContents, string $tooltipId=null) {
        $tooltip_selector = $this->getTooltipElementSelector($tooltipId);
        $browser->elsewhere('', function(Browser $body) use ($tooltip_selector, $expectedStringNotInTooltipContents) {
            $body
                ->assertVisible($tooltip_selector)
                ->assertDontSeeIn($tooltip_selector, $expectedStringNotInTooltipContents);
        });
    }

    protected function assertStringNotInTooltipContentsByTriggerElement(Browser $browser, string $expectedStringNotInTooltipContents, string $triggerElementSelector) {
        $tooltip_id = $this->getTooltipIdFromTriggerElement($browser, $triggerElementSelector);
        $this->assertStringNotInTooltipContents($browser, $expectedStringNotInTooltipContents, $tooltip_id);
    }

    protected function interactWithElementToTriggerTooltip(Browser $browser, string $triggerElementSelector): void {
        $browser->mouseover($triggerElementSelector);
    }

    private function getTooltipIdFromTriggerElement(Browser $browser, string $triggerElementSelector): string {
        return $browser->element($triggerElementSelector)->getAttribute('aria-describedby');
    }

    private function getTooltipElementSelector(string $tooltipId=null): string {
        return is_null($tooltipId) ? self::$SELECTOR_TOOLTIP : sprintf(self::$TEMPLATE_SELECTOR_TOOLTIP_ID, $tooltipId);
    }

}
