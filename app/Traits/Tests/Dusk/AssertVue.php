<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

trait AssertVue {

    /**
     * Used as a substitute for assertVue() and vueAttribute() methods
     *   located in vendor/laravel/dusk/src/Concerns/MakesAssertions.php
     *
     * @param Browser $browser
     * @param string  $selector
     * @param string  $vueKey
     * @param mixed   $expectedValue
     */
    public function assertVueAttribute(Browser $browser, string $selector, string $vueKey, $expectedValue){
        $fullSelector = $browser->resolver->format($selector);
        $actualValue = $browser->driver->executeScript(
            "var el = document.querySelector('".$fullSelector."');".
            "return typeof el.__vue__ === 'undefined' ".
            '? JSON.parse( JSON.stringify(el.__vueParentComponent.ctx.'.$vueKey.') )'.
            ': el.__vue__.'.$vueKey
        );

        $formattedValue = json_encode($expectedValue);
        PHPUnit::assertEquals(
            $expectedValue,
            $actualValue,
            "Did not see expected value [ {$formattedValue} ] at the key [ {$vueKey} ]."
        );
    }

}
