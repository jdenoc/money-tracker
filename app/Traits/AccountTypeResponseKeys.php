<?php

namespace App\Traits;

trait AccountTypeResponseKeys {

    // response keys
    private static $RESPONSE_KEY_ID = 'id';
    private static $RESPONSE_KEY_ERROR = 'error';

    // error keys
    private static $ERROR_ID = 0;
    private static $ERROR_MSG_NO_ERROR = '';
    private static $ERROR_MSG_NO_DATA = 'No data provided';
    private static $ERROR_MSG_MISSING_PROPERTY = 'Missing data: %s';
    private static $ERROR_MSG_DOES_NOT_EXIST = 'Account-type does not exist';
    private static $ERROR_MSG_INVALID_ACCOUNT = 'Account provided does not exist';
    private static $ERROR_MSG_INVALID_TYPE = 'Type provided is invalid';

    protected function fillMissingPropertyErrorMessage($missing_properties): string {
        return sprintf(self::$ERROR_MSG_MISSING_PROPERTY, json_encode($missing_properties));
    }

}
