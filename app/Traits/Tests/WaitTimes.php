<?php

namespace App\Traits\Tests;

trait WaitTimes {

    protected static $WAIT_SECONDS = 10;
    protected static $WAIT_SECONDS_LONG = 30;
    protected static $WAIT_TENTH_SECONDS_IN_MILLISECONDS = 100;     // 0.1 seconds
    protected static $WAIT_QUARTER_SECONDS_IN_MILLISECONDS = 250;   // 0.25 seconds
    protected static $WAIT_TWO_FIFTHS_OF_A_SECOND_IN_MILLISECONDS = 400; // 0.4 seconds
    protected static $WAIT_HALF_SECOND_IN_MILLISECONDS = 500;       // 0.5 seconds
    protected static $WAIT_ONE_SECOND_IN_MILLISECONDS = 1000;       // 1 seconds
    protected static $WAIT_ONE_TENTH_OF_A_SECOND_IN_MILLISECONDS = 100;  // 0.1 seconds

}
