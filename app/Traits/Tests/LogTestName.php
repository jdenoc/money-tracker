<?php

namespace App\Traits\Tests;

trait LogTestName {

    protected bool $can_log_test_name = true;

    public function runTestNameLogging(string $testName): void {
        $this->logTestNameStatement("[TEST-START] ".$testName);

        $this->beforeApplicationDestroyed(function() use ($testName) {
            $this->logTestNameStatement("[TEST-END] ".$testName);
        });
    }

    public function logTestNameStatement(string $statement): void {
        if ($this->can_log_test_name) {
            logger()->info($statement);
        }
    }

    public function dontLogTestName(): void {
        $this->can_log_test_name = false;
    }

}
