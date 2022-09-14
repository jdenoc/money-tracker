<?php

namespace App\Traits;

use App\Models\Tag;

trait EntryFilterKeys {

    // filter parameter keys
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
    // filter sorting keys
    private static $FILTER_KEY_SORT = 'sort';
    private static $FILTER_KEY_SORT_PARAMETER = 'parameter';
    private static $FILTER_KEY_SORT_DIRECTION = 'direction';

    /**
     * @param bool $include_tag_ids
     * @return array
     */
    public static function getFilterValidationRules($include_tag_ids = true){
        $filter_details = [
            static::$FILTER_KEY_START_DATE=>'date_format:Y-m-d',
            static::$FILTER_KEY_END_DATE=>'date_format:Y-m-d',
            static::$FILTER_KEY_ACCOUNT=>'integer',
            static::$FILTER_KEY_ACCOUNT_TYPE=>'integer',
            static::$FILTER_KEY_TAGS=>'array',
            static::$FILTER_KEY_EXPENSE=>'boolean',
            static::$FILTER_KEY_ATTACHMENTS=>'boolean',
            static::$FILTER_KEY_MIN_VALUE=>'numeric',
            static::$FILTER_KEY_MAX_VALUE=>'numeric',
            static::$FILTER_KEY_UNCONFIRMED=>'boolean',
            static::$FILTER_KEY_IS_TRANSFER=>'boolean'
        ];

        if($include_tag_ids){
            $tags = Tag::cache()->get('all');
            $tag_ids = $tags->pluck('id')->toArray();
            $filter_details['tags.*'] = 'in:'.implode(',', $tag_ids);
        }

        return $filter_details;
    }
}
