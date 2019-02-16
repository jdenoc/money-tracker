<?php

namespace Tests;

use App\Traits\Tests\StorageTestFiles;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase {

    use CreatesApplication;
    use StorageTestFiles;

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

    /**
     * @return array
     */
    public function getApiTags(){
        $tags_response = $this->get("/api/tags");
        return $this->removeCountFromApiResponse($tags_response->json());
    }

    public function getApiAccounts(){
        $accounts_response = $this->get("/api/accounts");
        return $this->removeCountFromApiResponse($accounts_response->json());
    }

    /**
     * @return array
     */
    public function getApiAccountTypes(){
        $account_types_response = $this->get("/api/account-types");
        return $this->removeCountFromApiResponse($account_types_response->json());
    }

    /**
     * @param int $entry_id
     * @return array
     */
    public function getApiEntry($entry_id){
        $entry_response = $this->get("/api/entry/".$entry_id);
        return $this->removeCountFromApiResponse($entry_response->json());
    }

    private function removeCountFromApiResponse($api_call_response){
        unset($api_call_response['count']);
        return $api_call_response;
    }

    /**
     * @param int $page_number
     * @return array
     */
    public function getApiEntries($page_number=0){
        $entries_response = $this->json('POST', '/api/entries/'.$page_number, ["sort"=>["parameter"=>"entry_date", "direction"=>"desc"]]);
        $entries = $entries_response->json();
        return $entries;
    }

}
