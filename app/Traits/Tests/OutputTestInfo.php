<?php

namespace App\Traits\Tests;

/**
 * This trait is useful for show what test was run and when.
 * The `artisan test` command also provides some of this information, but if the test dies or timesout, we may lose out place.
 */
trait OutputTestInfo {

    private static int $TEST_COUNT;

    protected static function initOutputTestInfo(): void {
        self::$TEST_COUNT = 1;
        fwrite(STDOUT, "[".date('c')."] ".get_called_class()."\n");
    }

    protected function outputTestName(): void {
        fwrite(STDOUT, "[".date('c')."]   - ".self::$TEST_COUNT.") ".$this->nameWithDataSet()."\n");
    }

    protected function incrementTestCount(): void {
        self::$TEST_COUNT++;
    }

}
