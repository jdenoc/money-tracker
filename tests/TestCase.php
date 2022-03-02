<?php

namespace Tests;

use App\Traits\Tests\OutputTestInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase {

    use CreatesApplication;
    use RefreshDatabase;
    use OutputTestInfo;

    public static function setUpBeforeClass(): void{
        self::initOutputTestInfo();
    }

    public function setUp(): void{
        $this->outputTestName();
        parent::setUp();
    }

    public function tearDown(): void{
        $this->truncateDatabaseTables();
        parent::tearDown();
        $this->incrementTestCount();
    }

    /**
     * @deprecated - use Response->json() instead
     * @param Response|TestResponse $response
     * @return array|null
     */
    protected function getResponseAsArray($response){
        $response_body = $response->getContent();
        return json_decode($response_body, true);
    }

    /**
     * @param string $date_string
     * @param string $format
     * @param string $assert_failure_message
     */
    protected function assertDateFormat($date_string, $format, $assert_failure_message=''){
        $date = \DateTime::createFromFormat($format, $date_string);
        $this->assertTrue($date->format($format) === $date_string, $assert_failure_message);
    }

    /**
     * @param string $expected_datetime
     * @param string $actual_datetime
     * @param string $assert_failure_message
     */
    protected function assertDatetimeWithinOneSecond($expected_datetime, $actual_datetime, $assert_failure_message=''){
        $expected_timestamp = strtotime($expected_datetime);
        $actual_timestamp = strtotime($actual_datetime);
        $this->assertTrue(abs($expected_timestamp - $actual_timestamp) <= 1, $assert_failure_message);
    }

    /**
     * @param TestResponse|Response $response
     * @param int $expected_status
     * @param string $error_message
     */
    protected function assertResponseStatus($response, $expected_status, $error_message=''){
        $actual_status = $response->getStatusCode();
        $failure_message = "Expected status code ".$expected_status." but received ".$actual_status.".\nResponse content: ".$response->getContent();
        $failure_message .= (empty($error_message)) ? '': "\n".$error_message;
        $this->assertTrue($actual_status === $expected_status, $failure_message);
    }

    /**
     * Truncates all database tables related to this connection, with the exception of the "migrations" table
     * @link http://stackoverflow.com/a/18910102/4152012
     */
    protected function truncateDatabaseTables(){
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach($tables as $table){
            // don't want to truncate the "migrations" table
            if ($table == 'migrations') {
                continue;
            }
            DB::table($table)->truncate();
        }
    }

}