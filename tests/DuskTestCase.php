<?php

namespace Tests;

use App\Traits\Tests\StorageTestFiles;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase {

    use CreatesApplication;
    use StorageTestFiles;

    const RESIZE_WIDTH_PX = 1400;
    const RESIZE_HEIGHT_PX = 2500;

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
     * @return void
     * @throws \Throwable
     */
    protected function setUp(){
        parent::setUp();

        $this->resizeBrowser();
        $this->seedDatabase();
    }

    /**
     * Sets the default browser width and height
     *
     * @throws \Throwable
     */
    protected function resizeBrowser(){
        $this->browse(function (Browser $browser){
            $browser->resize(self::RESIZE_WIDTH_PX, self::RESIZE_HEIGHT_PX);
        });
    }

    /**
     * Seed the database by calling the artisan command:
     *      artisan db:seed --class=UiSampleDatabaseSeeder
     */
    protected function seedDatabase(){
        Artisan::call('db:seed', ['--class'=>'UiSampleDatabaseSeeder']);
    }

    /**
     * @return array
     */
    public function getApiTags(){
        $tags_response = $this->get("/api/tags");
        return $this->removeCountFromApiResponse($tags_response->json());
    }

    /**
     * @return array
     */
    public function getApiInstitutions(){
        $institutions_response = $this->get('/api/institutions');
        return $this->removeCountFromApiResponse($institutions_response->json());
    }

    /**
     * @return array
     */
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

    /**
     * @param array $api_call_response
     * @return array
     */
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
        return $entries_response->json();
    }

}
