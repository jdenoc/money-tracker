<?php

namespace App\Traits\Tests\Dusk;

/**
 * Trait BulmaColors
 * @link https://bulma.io/documentation/customize/variables/#initial-variables
 *
 * @package App\Traits\Tests\Dusk
 */
trait BulmaColors {

    // colors
    public static $COLOR_BLACK_HEX = "#0a0a0a";
    public static $COLOR_BLACK_BIS_HEX = "#121212";
    public static $COLOR_BLACK_TER_HEX = "#242424";
    public static $COLOR_BLUE_HEX = '#485fc7';
    public static $COLOR_CYAN_HEX = '#3e8ed0';
    public static $COLOR_GREEN_HEX = '#48c78e';
    public static $COLOR_GREY_HEX = '#7a7a7a';
    public static $COLOR_GREY_DARKER_HEX = "#363636";
    public static $COLOR_GREY_DARK_HEX = "#4A4A4A";
    public static $COLOR_GREY_LIGHT_HEX = "#B5B5B5";
    public static $COLOR_GREY_LIGHTER_HEX = "#DBDBDB";
    public static $COLOR_GREY_LIGHTEST_HEX = "#ededed";
    public static $COLOR_ORANGE_HEX = '#ff470f';
    public static $COLOR_PURPLE_HEX = '#b86bff';
    public static $COLOR_RED_HEX = '#f14668';
    public static $COLOR_TURQUOISE_HEX = "#00D1B2";
    public static $COLOR_YELLOW_HEX = '#ffe08a';
    public static $COLOR_WHITE_HEX = "#FFFFFF";
    public static $COLOR_WHITE_BIS_HEX = "#FAFAFA";
    public static $COLOR_WHITE_TER_HEX = "#f5f5f5";

    // color alias'
    public static $COLOR_DANGER_HEX = '';
    public static $COLOR_DARK_HEX = '';
    public static $COLOR_INFO_HEX = '';
    public static $COLOR_LIGHT_HEX = '';
    public static $COLOR_LINK_HEX = '';
    public static $COLOR_PRIMARY_HEX = '';
    public static $COLOR_SUCCESS_HEX = '';
    public static $COLOR_WARNING_HEX = '';

    protected function initAliasBulmaColors(){
        static::$COLOR_DANGER_HEX = static::$COLOR_RED_HEX;
        static::$COLOR_DARK_HEX = static::$COLOR_GREY_DARKER_HEX;
        static::$COLOR_INFO_HEX = static::$COLOR_CYAN_HEX;
        static::$COLOR_LIGHT_HEX = static::$COLOR_WHITE_TER_HEX;
        static::$COLOR_LINK_HEX = static::$COLOR_BLUE_HEX;
        static::$COLOR_PRIMARY_HEX = static::$COLOR_TURQUOISE_HEX;
        static::$COLOR_SUCCESS_HEX = static::$COLOR_GREEN_HEX;
        static::$COLOR_WARNING_HEX = static::$COLOR_YELLOW_HEX;
    }

}
