<?php

namespace App\Traits;

trait InstitutionResponseKeys {

    private static $RESPONSE_KEY_ID = 'id';
    private static $RESPONSE_KEY_ERROR = 'error';

    private static $ERROR_ID = 0;
    private static $ERROR_MSG_NO_ERROR = '';
    private static $ERROR_MSG_NO_DATA = 'No data provided';
    private static $ERROR_MSG_DOES_NOT_EXIST = 'Institution does not exist';
    private static $ERROR_MSG_MISSING_PROPERTY = "Missing data: %s";

    protected function fillMissingPropertyErrorMessage($missing_properties): string {
        return sprintf(self::$ERROR_MSG_MISSING_PROPERTY, json_encode($missing_properties));
    }

}
