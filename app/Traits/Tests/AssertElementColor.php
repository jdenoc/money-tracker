<?php

namespace App\Traits\Tests;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

trait AssertElementColor {

    /**
     * Alternative spelling of method
     *
     * @param Browser $browser
     * @param string $element_selector
     * @param string $expected_colour
     */
    public function assertElementColor(Browser $browser, string $element_selector, string $expected_colour){
        $this->assertElementColour($browser, $element_selector, $expected_colour);
    }

    /**
     * @param Browser $browser
     * @param string $element_selector
     * @param string $expected_colour
     */
    public function assertElementColour(Browser $browser, string $element_selector, string $expected_colour){
        $element_hex_colour_script_output = $browser->script([
            'css_color = window.getComputedStyle(document.querySelector("'.$element_selector.'"), null).getPropertyValue("background-color");',
            'rgb = css_color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);',
            'if(rgb === null){
                hex = css_color;    // assuming that the CSS color returned is HEX
            } else {
                r = (parseInt(rgb[1]) < 16 ? "0" : "")+parseInt(rgb[1]).toString(16);
                g = (parseInt(rgb[2]) < 16 ? "0" : "")+parseInt(rgb[2]).toString(16);
                b = (parseInt(rgb[3]) < 16 ? "0" : "")+parseInt(rgb[3]).toString(16);
                hex = "#"+r+g+b;
            }',
            'return hex;'
        ]);
        $element_hex_colour = end($element_hex_colour_script_output);
        PHPUnit::assertEquals(strtoupper($expected_colour), strtoupper($element_hex_colour), "Expected colour [$expected_colour] does not match actual colour [".$element_hex_colour."] of element [$element_selector]");
    }
}
