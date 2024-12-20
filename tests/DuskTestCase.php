<?php

namespace Tests;

use App\Models\Entry;
use App\Traits\EntryFilterKeys;
use App\Traits\Tests\LogTestName;
use App\Traits\Tests\OutputTestInfo;
use App\Traits\Tests\StorageTestFiles;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
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
    public static function prepare() {
        // static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return RemoteWebDriver
     */
    protected function driver() {
        return RemoteWebDriver::create(
            "http://selenium:4444/wd/hub",
            DesiredCapabilities::chrome()
        );
    }

    protected function newBrowser($driver) {
        return new ResizedBrowser($driver);
    }

    ///**
    // * Replaces default phpunit test name.
    // * The default phpunit test name was applied to console logs and screenshots.
    // * These files were then processed by other scripts.
    // * There were spaces and quotation marks causes these other scripts to fail.
    // *
    // * @param bool $withDataSet
    // * @return string|null
    // */
    //public function getName(bool $withDataSet = true): string {
    //    $test_name = parent::getName($withDataSet);
    //    $test_name = str_replace([" ", '"'], ["-", ''], $test_name);
    //    return $test_name;
    //}

    public static function setUpBeforeClass(): void {
        self::initOutputTestInfo();
    }

    /**
     * @throws \Throwable
     */
    protected function setUp(): void {
        $this->outputTestName();
        parent::setUp();

        Browser::$storeScreenshotsAt = Storage::disk('tests')->path('dusk/screenshots');
        Browser::$storeConsoleLogAt = Storage::disk('tests')->path('dusk/console');
        Browser::$storeSourceAt =  Storage::disk('tests')->path('dusk/source');
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->incrementTestCount();
    }

    protected function setUpTraits() {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[\App\Traits\Tests\LogTestName::class])) {
            $this->runTestNameLogging($this->nameWithDataSet());
        }

        if (isset($uses[\App\Traits\Tests\WithTailwindColors::class])) {
            $this->setupTailwindColors();
        }

        return parent::setUpTraits();
    }

    public function getApiTags(): array {
        $tags_response = $this->get("/api/tags");
        return $this->removeCountFromApiResponse($tags_response->json());
    }

    public function getApiInstitutions(): array {
        $institutions_response = $this->get('/api/institutions');
        return $this->removeCountFromApiResponse($institutions_response->json());
    }

    public function getApiAccounts(): array {
        $accounts_response = $this->get("/api/accounts");
        return $this->removeCountFromApiResponse($accounts_response->json());
    }

    public function getApiAccountTypes(): array {
        $account_types_response = $this->get("/api/account-types");
        return $this->removeCountFromApiResponse($account_types_response->json());
    }

    public function getApiEntry(int $entry_id): array {
        $entry_response = $this->get("/api/entry/".$entry_id);
        return $this->removeCountFromApiResponse($entry_response->json());
    }

    /**
     * @param array $api_call_response
     */
    public function removeCountFromApiResponse($api_call_response): array {
        unset($api_call_response['count']);
        return $api_call_response;
    }

    public function getApiEntries(int $page_number = 0, array $filter_data = []): array {
        // See resources/js/stores/entries.js:22-44
        // See app/Traits/EntryFilterKeys.php:9-24
        $sort = [self::$FILTER_KEY_SORT => [
            self::$FILTER_KEY_SORT_PARAMETER => "entry_date",
            self::$FILTER_KEY_SORT_DIRECTION => Entry::DEFAULT_SORT_DIRECTION,
        ]];
        $filter_data = array_merge($filter_data, $sort);
        $entries_response = $this->postJson('/api/entries/'.$page_number, $filter_data);
        return $entries_response->json();
    }

    //public static function assertContains($needle, iterable $haystack, string $message = ''): void {
    //    if (empty($message)) {
    //        $message = sprintf("Failed asserting that `%s` contains `%s`.", json_encode($haystack), $needle);
    //    }
    //    parent::assertContains($needle, $haystack, $message);
    //}

}
