<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;

abstract class TestCase extends BaseTestCase {

    use CreatesApplication;

     /**
     * @param Response $response
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

}
