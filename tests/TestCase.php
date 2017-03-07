<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase {

    use CreatesApplication;

    public function tearDown(){
        $this->truncateDatabaseTables();
        parent::tearDown();
    }

    /**
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