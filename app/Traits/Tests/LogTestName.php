<?php

namespace App\Traits\Tests;

trait LogTestName {

    protected $can_log_test_name = true;

    /**
     * @param string $testName
     */
    public function runTestNameLogging($testName){
        $this->logTestNameStatement("[TEST-START] ".$testName);

        $this->beforeApplicationDestroyed(function() use ($testName) {
            $this->logTestNameStatement("[TEST-END] ".$testName);
        });
    }

    /**
     * @param string $statement
     */
    public function logTestNameStatement($statement){
        if($this->can_log_test_name){
            logger($statement);
        }
    }

    public function dontLogTestName(){
        $this->can_log_test_name = false;
    }

}
