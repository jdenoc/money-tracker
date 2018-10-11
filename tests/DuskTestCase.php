<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase {

    use CreatesApplication;

    const TEST_STORAGE_FILE_PATH = "app/test/download.jpg";

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare(){
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver(){
        return RemoteWebDriver::create(
             'http://selenium:4444/wd/hub', DesiredCapabilities::chrome(), 5000, 10000
        );
    }

    public function getApiTags(){
        $tags_response = $this->get("/api/tags");
        $tags = $tags_response->json();
        unset($tags['count']);
        return $tags;
    }

    public function getApiAccountTypes(){
        $account_types_response = $this->get("/api/account-types");
        $account_types = $account_types_response->json();
        unset($account_types['count']);
        return $account_types;
    }

    public function getApiEntry($entry_id){
        $entry_response = $this->get("/api/entry/".$entry_id);
        $entry = $entry_response->json();
        return $entry;
    }

}
