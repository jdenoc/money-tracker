<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\AssertElementColor;
use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait ToggleButton {

    use AssertElementColor;
    use WaitTimes;

    private static $SELECTOR_TOGGLE_BUTTON_SWITCH_CORE = ".v-switch-core";

    /**
     * @param Browser $browser
     * @param string $selector
     * @param string $label
     * @param string|null $color
     */
    public function assertToggleButtonState(Browser $browser, string $selector, string $label, $color=null){
        $browser
            ->assertVisible($selector)
            ->assertSeeIn($selector, $label);
        if(!is_null($color)){
            $element_selector = $browser->resolver->format($selector);
            $this->assertElementBackgroundColour($browser, $element_selector.' '.self::$SELECTOR_TOGGLE_BUTTON_SWITCH_CORE, $color);
        }
    }

    /**
     * @param Browser $browser
     * @param string $selector
     */
    public function toggleToggleButton(Browser $browser, string $selector){
        $browser
            ->click($selector)
            ->pause(self::$WAIT_HALF_SECOND_IN_MILLISECONDS);   // wait for transition to complete
    }

}
