<?php

namespace App\Traits\Tests;

use App\Account;
use App\Traits\EntryFilterKeys;
use Faker\Generator as FakerGenerator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;

trait GenerateFilterTestCases {

    use EntryFilterKeys;

    public function generateFilterTestCases($faker):array{
        $filter = [];
        $filter['no filter'] = [[]];

        $end_date = $faker->date();
        $start_date = $faker->date("Y-m-d", $end_date);
        $max_value = $faker->randomFloat(2, 0, 50);
        $min_value = $faker->randomFloat(2, 0, $max_value);

        $filter_details = [
            self::$FILTER_KEY_START_DATE=>$start_date,
            self::$FILTER_KEY_END_DATE=>$end_date,
            self::$FILTER_KEY_ACCOUNT=>0,       // will be set later
            self::$FILTER_KEY_ACCOUNT_TYPE=>0,  // will be set later
            self::$FILTER_KEY_TAGS=>[],         // will be set later
            self::$FILTER_KEY_EXPENSE=>$faker->boolean,
            self::$FILTER_KEY_ATTACHMENTS=>$faker->boolean,
            self::$FILTER_KEY_MIN_VALUE=>$min_value,
            self::$FILTER_KEY_MAX_VALUE=>$max_value,
            self::$FILTER_KEY_UNCONFIRMED=>$faker->boolean,
            self::$FILTER_KEY_IS_TRANSFER=>$faker->boolean,
        ];

        // confirm all filters in EntryFilterKeys trait are listed here
        $current_filters = self::getFilterValidationRules(false);
        foreach(array_keys($current_filters) as $existing_filter){
            Assert::assertArrayHasKey($existing_filter, $filter_details);
        }

        // individual filter requests
        foreach($filter_details as $filter_name=>$filter_value){
            // confirm all filters listed in test are in EntryFilterKeys trait
            Assert::assertArrayHasKey($filter_name, $current_filters);

            // adding a switch to catch all eventualities for boolean conditions
            switch($filter_name){
                case self::$FILTER_KEY_EXPENSE:
                case self::$FILTER_KEY_ATTACHMENTS:
                case self::$FILTER_KEY_UNCONFIRMED:
                case self::$FILTER_KEY_IS_TRANSFER:
                    $filter["filtering [".$filter_name.":true]"] = [
                        [$filter_name=>true]
                    ];
                    $filter["filtering [".$filter_name.":false]"] = [
                        [$filter_name=>false]
                    ];
                    break;
                default:
                    $filter["filtering [".$filter_name."]"] = [
                        [$filter_name=>$filter_value]
                    ];
            }
        }

        // batch of filter requests
        $batched_filter_details = array_rand($filter_details, 3);   // NOTE: this can't use the faker method. It will cause warnings in tests to occur.
        $filter["filtering [".implode(",", $batched_filter_details).']'] = [array_intersect_key($filter_details, array_flip($batched_filter_details))];

        // all filter requests
        $filter["filtering [".implode(",", array_keys($filter_details)).']'] = [$filter_details];

        return $filter;
    }

    /**
     * Because the data provider method is called before the test, we are unlikely to have the same tags setup
     * This method is called at the start of each test and gathers the tags that are available, then assigns them to the "filter" array
     * account_type and account IDs are also randomly selected and assigned to the "filter" array.
     *
     * @param FakerGenerator  $faker
     * @param array           $filter_details
     * @param Account|null    $account
     * @param Collection|null $tags
     *
     * @return array
     */
    protected function setTestSpecificFilters(FakerGenerator $faker, array $filter_details, $account=null, $tags=null):array{
        if(key_exists(self::$FILTER_KEY_TAGS, $filter_details)){
            $tag_ids = $tags->pluck('id')->toArray();
            $filter_details[self::$FILTER_KEY_TAGS] = $faker->randomElements($tag_ids, $faker->numberBetween(1, count($tag_ids)));
        }
        if(key_exists(self::$FILTER_KEY_ACCOUNT_TYPE, $filter_details)){
            $account_types = $account->account_types()->pluck('id')->toArray();
            $filter_details[self::$FILTER_KEY_ACCOUNT_TYPE] = $faker->randomElement($account_types);
        }
        if(key_exists(self::$FILTER_KEY_ACCOUNT, $filter_details)){
            $filter_details[self::$FILTER_KEY_ACCOUNT] = $account->id;
        }
        return $filter_details;
    }


}
