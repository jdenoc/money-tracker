<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Laravel\Dusk\Browser;

trait Tooltip {

    use WaitTimes;

    /**
     * This method takes a lot of code from predefined Browser methods
     * This allows us to do some work around testing without having to worry about css selector chaining
     *
     * @param Browser $browser
     * @param RemoteWebElement $element
     * @param string $tooltip_text
     */
    private function assertTooltip(Browser $browser, $element, string $tooltip_text){
        $browser->driver->getMouse()->mouseMove($element->getCoordinates());    // move mouse over element
        $browser->pause(self::$WAIT_HALF_SECOND_IN_MILLISECONDS);               // wait for element to update
        $tooltip_id = $element->getAttribute('aria-describedby');  // get the tooltip element id
        $this->assertNotEmpty($tooltip_id);

        // confirm tooltip element is visible then get text
        $selector_tooltip = "#".$tooltip_id;
        $css_prefix = $browser->resolver->prefix;
        $browser->resolver->prefix = '';
        $browser->assertVisible($selector_tooltip);
        $tooltip_text_from_element = $browser->text($selector_tooltip);
        $this->assertStringContainsString($tooltip_text, $tooltip_text_from_element);
        $browser->resolver->prefix = $css_prefix;
    }

}
