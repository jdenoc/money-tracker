<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait ResizeBrowser {

    protected static $RESIZE_BROWSER_WIDTH_PX = 1366;
    protected static $RESIZE_BROWSER_HEIGHT_PX = 784;
    protected static $FORCE_NO_RESIZE = false;

    /**
     * Sets the default browser width and height
     *
     * @throws \Throwable
     */
    protected function resizeBrowser(){
        $this->browse(function (Browser $browser){
            error_log($this->getName().' resized to: w:'.self::$RESIZE_BROWSER_WIDTH_PX.'; h:'.self::$RESIZE_BROWSER_HEIGHT_PX);
            if(!self::$FORCE_NO_RESIZE){
                $browser->resize(static::$RESIZE_BROWSER_WIDTH_PX, static::$RESIZE_BROWSER_HEIGHT_PX);
            }
        });
    }

}
