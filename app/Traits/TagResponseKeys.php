<?php

namespace App\Traits;

trait TagResponseKeys {

    private static $RESPONSE_KEY_ID = 'id';
    private static $RESPONSE_KEY_ERROR = 'error';

    private static $ERROR_ID = 0;
    private static $ERROR_MSG_NO_ERROR = '';
    private static $ERROR_MSG_NO_DATA = 'No data provided';
    private static $ERROR_MSG_DOES_NOT_EXIST = 'Tag does not exist';

}
