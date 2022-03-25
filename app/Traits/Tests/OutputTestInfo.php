<?php

namespace App\Traits\Tests;

trait OutputTestInfo {

    private static int $TEST_COUNT;

    protected static function initOutputTestInfo(){
        self::$TEST_COUNT = 1;
        fwrite(STDOUT, get_called_class()."\n");
    }

    protected function outputTestName(){
        fwrite(STDOUT, "  - ".self::$TEST_COUNT.") ".$this->getName()." [start:".date('c')."]\n");
    }

    protected function incrementTestCount(){
        self::$TEST_COUNT++;
    }

}