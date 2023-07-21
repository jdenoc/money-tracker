<?php

namespace App\Traits\Tests;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;

trait AssertElementColor {

    private static string $COLOR_ASSERT_ERROR_MESSAGE_TEMPLATE = "Expected colour [%s] does not match actual colour [%s] of element [%s]";

    public function assertElementBackgroundColor(Browser $browser, string $element_selector, string $expected_colour) {
        $this->assertElementBackgroundColour($browser, $element_selector, $expected_colour);
    }

    public function assertElementBackgroundColour(Browser $browser, string $element_selector, string $expected_colour) {
        $full_selector = $browser->resolver->format($element_selector);
        $element_hex_colour = $this->getElementColor($browser, $full_selector, 'background-color', false);
        PHPUnit::assertEquals(strtoupper($expected_colour), strtoupper($element_hex_colour), sprintf(static::$COLOR_ASSERT_ERROR_MESSAGE_TEMPLATE, $expected_colour, $element_hex_colour, $full_selector));
    }

    public function assertParentElementBackgroundColor(Browser $browser, string $element_selector, string $expected_colour) {
        $this->assertParentElementBackgroundColour($browser, $element_selector, $expected_colour);
    }

    public function assertParentElementBackgroundColour(Browser $browser, string $element_selector, string $expected_colour) {
        $full_selector = $browser->resolver->format($element_selector);
        $element_hex_colour = $this->getElementColor($browser, $full_selector, 'background-color', true);
        PHPUnit::assertEquals(strtoupper($expected_colour), strtoupper($element_hex_colour), sprintf(static::$COLOR_ASSERT_ERROR_MESSAGE_TEMPLATE, $expected_colour, $element_hex_colour, $full_selector));
    }

    public function assertElementTextColor(Browser $browser, string $element_selector, string $expected_colour) {
        $this->assertElementTextColour($browser, $element_selector, $expected_colour);
    }

    public function assertElementTextColour(Browser $browser, string $element_selector, string $expected_colour) {
        $full_selector = $browser->resolver->format($element_selector);
        $element_hex_colour = $this->getElementColor($browser, $full_selector, 'color', false);
        PHPUnit::assertEquals(strtoupper($expected_colour), strtoupper($element_hex_colour), sprintf(static::$COLOR_ASSERT_ERROR_MESSAGE_TEMPLATE, $expected_colour, $element_hex_colour, $full_selector));
    }

    public function assertParentElementTextColor(Browser $browser, string $element_selector, string $expected_colour) {
        $this->assertParentElementTextColour($browser, $element_selector, $expected_colour);
    }

    public function assertParentElementTextColour(Browser $browser, string $element_selector, string $expected_colour) {
        $full_selector = $browser->resolver->format($element_selector);
        $element_hex_colour = $this->getElementColor($browser, $full_selector, 'color', true);
        PHPUnit::assertEquals(strtoupper($expected_colour), strtoupper($element_hex_colour), sprintf(static::$COLOR_ASSERT_ERROR_MESSAGE_TEMPLATE, $expected_colour, $element_hex_colour, $full_selector));
    }

    private function getElementColor(Browser $browser, string $element_selector, string $attribute, bool $is_parent_of_selector): string {
        $document_query_selector = 'document.querySelector("'.$element_selector.'")'.($is_parent_of_selector ? '.parentNode' : '');

        $script = <<<JS
css_color = window.getComputedStyle($document_query_selector, null).getPropertyValue('$attribute');
rgba = css_color.match(/^rgba\((\d+),\s*(\d+),\s*(\d+),\s*(\d*\.?\d*)\)$/);
if(rgba === null){
  rgb = css_color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
  if(rgb === null){
    hex = css_color;    // assuming that the CSS color returned is HEX
  } else {
    rgba = rgb;
    rgba[4] = 1;
  }
}

if(rgba !== null){
  r = (parseInt(rgba[1]) < 16 ? "0" : "")+parseInt(rgba[1]).toString(16);
  g = (parseInt(rgba[2]) < 16 ? "0" : "")+parseInt(rgba[2]).toString(16);
  b = (parseInt(rgba[3]) < 16 ? "0" : "")+parseInt(rgba[3]).toString(16);
  hex = "#"+r+g+b;
}

return hex;
JS;
        $element_hex_colour_script_output = $browser->script($script);
        return end($element_hex_colour_script_output);
    }

}
