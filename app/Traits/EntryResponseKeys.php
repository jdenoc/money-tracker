<?php

namespace App\Traits;

trait EntryResponseKeys {

    private static $ERROR_ENTRY_ID = 0;
    private static $RESPONSE_SAVE_KEY_ID = 'id';
    private static $RESPONSE_SAVE_KEY_ERROR = 'error';
    private static $RESPONSE_FILTER_KEY_ERROR = 'error';
    private static $ERROR_MSG_SAVE_ENTRY_NO_ERROR = '';
    private static $ERROR_MSG_SAVE_ENTRY_NO_DATA = "No data provided";
    private static $ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY = "Missing data: %s";
    private static $ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE = "Account type provided does not exist";
    private static $ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST = "Entry does not exist";
    private static $ERROR_MSG_SAVE_TRANSFER_BOTH_EXTERNAL = "A transfer can not consist with both entries belonging to external accounts";
    private static $ERROR_MSG_FILTER_INVALID = 'invalid filter provided';

}
