<?php

namespace App\Traits;

use App\Models\Tag;

trait EntryFilterKeys {

    private static $FILTER_KEY_ACCOUNT = 'account';
    private static $FILTER_KEY_ACCOUNT_TYPE = 'account_type';
    private static $FILTER_KEY_ATTACHMENTS = 'attachments';
    private static $FILTER_KEY_END_DATE = 'end_date';
    private static $FILTER_KEY_EXPENSE = 'expense';
    private static $FILTER_KEY_IS_TRANSFER = 'is_transfer';
    private static $FILTER_KEY_MAX_VALUE = 'max_value';
    private static $FILTER_KEY_MIN_VALUE = 'min_value';
    private static $FILTER_KEY_START_DATE = 'start_date';
    private static $FILTER_KEY_TAGS = 'tags';
    private static $FILTER_KEY_UNCONFIRMED = 'unconfirmed';
    private static $FILTER_KEY_SORT = 'sort';
    private static $FILTER_KEY_SORT_PARAMETER = 'parameter';
    private static $FILTER_KEY_SORT_DIRECTION = 'direction';

    /**
     * @param bool $include_tag_ids
     * @return array
     */
    public static function getFilterValidationRules($include_tag_ids = true){
        $filter_details = [
            self::$FILTER_KEY_START_DATE=>'date_format:Y-m-d',
            self::$FILTER_KEY_END_DATE=>'date_format:Y-m-d',
            self::$FILTER_KEY_ACCOUNT=>'integer',
            self::$FILTER_KEY_ACCOUNT_TYPE=>'integer',
            self::$FILTER_KEY_TAGS=>'array',
            self::$FILTER_KEY_EXPENSE=>'boolean',
            self::$FILTER_KEY_ATTACHMENTS=>'boolean',
            self::$FILTER_KEY_MIN_VALUE=>'numeric',
            self::$FILTER_KEY_MAX_VALUE=>'numeric',
            self::$FILTER_KEY_UNCONFIRMED=>'boolean',
            self::$FILTER_KEY_IS_TRANSFER=>'boolean'
        ];

        if($include_tag_ids){
            $tags = Tag::all();
            $tag_ids = $tags->pluck('id')->toArray();
            $filter_details['tags.*'] = 'in:'.implode(',', $tag_ids);
        }

        return $filter_details;
    }
}
