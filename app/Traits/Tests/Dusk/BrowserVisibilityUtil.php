<?php

namespace App\Traits\Tests\Dusk;

use Laravel\Dusk\Browser;

trait BrowserVisibilityUtil {

    public function isVisibleInViewport(Browser $browser, string $element) {
        $isInViewportFunc = <<<JS
let elementBoundaries = document.querySelector('$element').getBoundingClientRect();
return elementBoundaries.top > 0
  && elementBoundaries.left > 0
  && elementBoundaries.bottom <= (window.innerHeight || document.documentElement.clientHeight)
  && elementBoundaries.right <= (window.innerWidth || document.documentElement.clientWidth);
JS;
        $js_output = $browser->script([$isInViewportFunc]);
        return $js_output[0];
    }

}
