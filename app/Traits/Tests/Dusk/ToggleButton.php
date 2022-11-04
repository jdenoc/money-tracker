<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\AssertElementColor;
use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait ToggleButton {
    use AssertElementColor;
    use WaitTimes;

    private static string $SELECTOR_TOGGLE_BUTTON = ".vue-toggles";

    public function assertToggleButtonState(Browser $browser, string $selector, string $label, ?string $color=null): void {
        $browser
            ->assertVisible($selector)
            ->assertSeeIn($selector, $label);
        if (!is_null($color)) {
            $this->assertElementBackgroundColour($browser, $selector.self::$SELECTOR_TOGGLE_BUTTON, $color);
        }
    }

    public function toggleToggleButton(Browser $browser, string $selector): void {
        $browser
            ->click($selector)
            ->pause($this->toggleButtonTransitionTimeInMilliseconds());   // wait for transition to complete
    }

    private function toggleButtonTransitionTimeInMilliseconds(): int {
        return self::$WAIT_QUARTER_SECONDS_IN_MILLISECONDS;
    }

}
