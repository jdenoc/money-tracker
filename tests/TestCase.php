<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;
    use RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        Cache::flush();
    }

    protected function assertDateFormat(string $date_string, string $format, string $assert_failure_message = ''): void {
        $date = \DateTime::createFromFormat($format, $date_string);
        $this->assertTrue($date->format($format) === $date_string, $assert_failure_message);
    }

    protected function assertDatetimeWithinOneSecond(string $expected_datetime, string $actual_datetime, string $assert_failure_message = ''): void {
        $expected_timestamp = strtotime($expected_datetime);
        $actual_timestamp = strtotime($actual_datetime);
        $this->assertTrue(abs($expected_timestamp - $actual_timestamp) <= 1, $assert_failure_message);
    }

    /**
     * @param TestResponse|Response $response
     */
    protected function assertResponseStatus($response, int $expected_status, string $error_message = ''): void {
        $actual_status = $response->getStatusCode();
        $failure_message = "Expected status code ".$expected_status." but received ".$actual_status.".\nResponse content: ".$response->getContent();
        $failure_message .= (empty($error_message)) ? '' : "\n".$error_message;
        $this->assertTrue($actual_status === $expected_status, $failure_message);
    }

}
