<?php

namespace App\Traits\Tests\Dusk;

trait BrowserDimensions {

    // Average browser window size
    // source: https://css-tricks.com/screen-resolution-notequalto-browser-window/#:~:text=Average%20browser%20window%20size%20%3D%201366%20x%20784
    public static int $DEFAULT_BROWSER_WIDTH_PX = 1366;
    public static int $DEFAULT_BROWSER_HEIGHT_PX = 784;

    // threshold values taken from https://tailwindcss.com/docs/responsive-design
    private static int $MAX_SM_BROWSER_WIDTH_PX = 767;

}
