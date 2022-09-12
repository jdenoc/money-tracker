<?php

namespace Tests;

use App\Models\Entry;
use App\Traits\EntryFilterKeys;
use App\Traits\Tests\LogTestName;
use App\Traits\Tests\OutputTestInfo;
use App\Traits\Tests\StorageTestFiles;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tests\Browser\ResizedBrowser;

abstract class DuskTestCase extends BaseTestCase {

    use CreatesApplication;
    use EntryFilterKeys;
    use LogTestName;
    use OutputTestInfo;
    use StorageTestFiles;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare(){
//        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return RemoteWebDriver
     */
    protected function driver(){
        return RemoteWebDriver::create(
            "http://selenium:4444/wd/hub",
            DesiredCapabilities::chrome()
        );
    }

    protected function newBrowser($driver){
        return new ResizedBrowser($driver);
    }

    /**
     * Replaces default phpunit test name.
     * The default phpunit test name was applied to console logs and screenshots.
     * These files were then processed by other scripts.
     * There were spaces and quotation marks causes these other scripts to fail.
     *
     * @param bool $withDataSet
     * @return string|null
     */
    public function getName(bool $withDataSet = true): string {
        $test_name = parent::getName($withDataSet);
        $test_name = str_replace([" ", '"'], ["-", ''], $test_name);
        return $test_name;
    }

    public static function setUpBeforeClass(): void{
        self::initOutputTestInfo();
    }

    /**
     * @return void
     * @throws \Throwable
     */
    protected function setUp(): void{
        $this->outputTestName();
        parent::setUp();
    }

    protected function tearDown(): void{
        parent::tearDown();
        $this->incrementTestCount();
    }

    protected function setUpTraits(){
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[\App\Traits\Tests\LogTestName::class])){
            $this->runTestNameLogging($this->getName(true));
        }

        if(isset($uses[\App\Traits\Tests\InjectDatabaseStateIntoException::class])){
            $this->prepareFailureExceptionForDatabaseInjection();
        }

        if(isset($uses[\App\Traits\Tests\WithTailwindColors::class])){
            $this->setupTailwindColors();
        }

        return parent::setUpTraits();
    }

    /**
     * @return array
     */
    public function getApiTags():array{
        $tags_response = $this->get("/api/tags");
        return $this->removeCountFromApiResponse($tags_response->json());
    }

    /**
     * @return array
     */
    public function getApiInstitutions():array{
        $institutions_response = $this->get('/api/institutions');
        return $this->removeCountFromApiResponse($institutions_response->json());
    }

    /**
     * @return array
     */
    public function getApiAccounts():array{
        $accounts_response = $this->get("/api/accounts");
        return $this->removeCountFromApiResponse($accounts_response->json());
    }

    /**
     * @return array
     */
    public function getApiAccountTypes():array{
        $account_types_response = $this->get("/api/account-types");
        return $this->removeCountFromApiResponse($account_types_response->json());
    }

    /**
     * @param int $entry_id
     * @return array
     */
    public function getApiEntry(int $entry_id):array{
        $entry_response = $this->get("/api/entry/".$entry_id);
        return $this->removeCountFromApiResponse($entry_response->json());
    }

    /**
     * @param array $api_call_response
     * @return array
     */
    public function removeCountFromApiResponse($api_call_response):array{
        unset($api_call_response['count']);
        return $api_call_response;
    }

    /**
     * @param int $page_number
     * @param array $filter_data
     * @return array
     */
    public function getApiEntries(int $page_number=0, array $filter_data=[]):array{
        // See resources/js/entries.js:16-38
        // See app/Traits/EntryFilterKeys.php:7-22
        $sort = [self::$FILTER_KEY_SORT=>[
            self::$FILTER_KEY_SORT_PARAMETER=>"entry_date",
            self::$FILTER_KEY_SORT_DIRECTION=>Entry::DEFAULT_SORT_DIRECTION
        ]];
        $filter_data = array_merge($filter_data, $sort);
        $entries_response = $this->json('POST', '/api/entries/'.$page_number, $filter_data);
        return $entries_response->json();
    }

    public static function assertContains($needle, iterable $haystack, string $message = ''): void{
        if(empty($message)){
            $message = sprintf("Failed asserting that %s contains %s.", json_encode($haystack), $needle);
        }
        parent::assertContains($needle, $haystack, $message);
    }

}
