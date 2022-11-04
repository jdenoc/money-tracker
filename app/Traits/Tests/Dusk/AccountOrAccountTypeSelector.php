<?php

namespace App\Traits\Tests\Dusk;

use App\Traits\Tests\WaitTimes;
use Laravel\Dusk\Browser;

trait AccountOrAccountTypeSelector {
    use WaitTimes;

    /**
     * @param Browser $component
     * @param string $select_selector
     *
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    private function waitUntilSelectLoadingIsMissing(Browser $component, string $select_selector) {
        $class_loading = '.loading';
        $parent_visible_script = <<<JS
return document.querySelector('{$component->resolver->prefix}').querySelector('$select_selector').parentNode.querySelector('$class_loading').offsetParent === null;
JS;
        $component->waitUntil($parent_visible_script, self::$WAIT_SECONDS);
    }

}
