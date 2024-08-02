<?php

namespace Tests\Browser;

use App\Traits\Tests\Dusk\BrowserDimensions;
use Facebook\WebDriver\WebDriverDimension;
use Illuminate\Support\Facades\Log;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns;

class ResizedBrowser extends Browser {
    use BrowserDimensions;
    // carried over from original Browser class
    use Concerns\InteractsWithAuthentication;
    use Concerns\InteractsWithCookies;
    use Concerns\InteractsWithElements;
    use Concerns\InteractsWithJavascript;
    use Concerns\InteractsWithMouse;
    use Concerns\MakesAssertions;
    use Concerns\MakesUrlAssertions;
    use Concerns\WaitsForElements;

    public function visit($url) {
        return parent::visit($url)
            ->resize(self::$DEFAULT_BROWSER_WIDTH_PX, self::$DEFAULT_BROWSER_HEIGHT_PX)
            ->disableFitOnFailure();    // stop screenshots being resized
    }

    public function resize($width, $height) {
        $width = $width ?? static::$DEFAULT_BROWSER_WIDTH_PX;
        $height = $height ?? static::$DEFAULT_BROWSER_HEIGHT_PX;

        // set browser window dimensions
        parent::resize($width, $height);
        // get browser & viewport dimensions
        $browser_size = $this->getBrowserWindowSize();
        $viewport_size = $this->getBrowserViewportSize();

        $adjusted_width = ($browser_size->getWidth() - $viewport_size->getWidth() + $width);
        $adjusted_height = ($browser_size->getHeight() - $viewport_size->getHeight() + $height);
        parent::resize($adjusted_width, $adjusted_height);

        Log::debug("resized window to width:$adjusted_width; height:$adjusted_height");
        $viewport_size = $this->getBrowserViewportSize();
        Log::debug("viewport dimensions: width:" . $viewport_size->getWidth(). "; height:" . $viewport_size->getHeight());

        return $this;
    }

    private function getBrowserWindowSize(): WebDriverDimension {
        return $this->driver->manage()->window()->getSize();
    }

    private function getBrowserViewportSize(): WebDriverDimension {
        $viewport_dimensions = $this->script([
            "return window.innerWidth;",
            "return window.innerHeight;"
        ]);

        return new WebDriverDimension(
            $viewport_dimensions[0],
            $viewport_dimensions[1],
        );
    }

}
