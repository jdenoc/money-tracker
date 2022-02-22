<?php

namespace Tests\Feature\Api\Post;

use App\AccountType;
use App\Entry;
use App\Tag;
use App\Traits\EntryFilterKeys;
use App\Traits\MaxEntryResponseValue;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class PostEntriesTest extends \Tests\Feature\Api\ListEntriesBase {

    use EntryFilterKeys;
    use MaxEntryResponseValue;

    public function providerPostEntriesFilter(){
        // need to call setUp() before running through a data provider method
        // environment needs setting up and isn't until setUp() is called
        //$this->setUp();
        // We can no longer call setUp() as a work around
        // it caused the database to populate and in doing so we caused some tests to fail.
        // Said tests failed because they were testing the absence of database values.
        $this->initialiseApplication();
        $this->setUpFaker();

        $filter = [];
        $filter['no filter'] = [[]];

        $end_date = $this->faker->date();
        $start_date = $this->faker->date("Y-m-d", $end_date);
        $max_value = $this->faker->randomFloat(2, 0, 50);
        $min_value = $this->faker->randomFloat(2, 0, $max_value);

        $filter_details = [
            self::$FILTER_KEY_START_DATE=>$start_date,
            self::$FILTER_KEY_END_DATE=>$end_date,
            self::$FILTER_KEY_ACCOUNT=>0,       // will be set later
            self::$FILTER_KEY_ACCOUNT_TYPE=>0,  // will be set later
            self::$FILTER_KEY_TAGS=>[],         // will be set later
            self::$FILTER_KEY_EXPENSE=>$this->faker->boolean(),
            self::$FILTER_KEY_ATTACHMENTS=>$this->faker->boolean(),
            self::$FILTER_KEY_MIN_VALUE=>$min_value,
            self::$FILTER_KEY_MAX_VALUE=>$max_value,
            self::$FILTER_KEY_UNCONFIRMED=>$this->faker->boolean(),
            self::$FILTER_KEY_IS_TRANSFER=>$this->faker->boolean(),
        ];

        // confirm all filters in EntryFilterKeys trait are listed here
        $current_filters = self::getFilterValidationRules(false);
        foreach(array_keys($current_filters) as $existing_filter){
            $this->assertArrayHasKey($existing_filter, $filter_details);
        }

        // individual filter requests
        foreach($filter_details as $filter_name=>$filter_value){
            // confirm all filters listed in test are in EntryFilterKeys trait
            $this->assertArrayHasKey($filter_name, $current_filters);

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
     * @dataProvider providerPostEntriesFilter
     * @param $filter_details
     */
    public function testPostEntriesThatDoNotExist($filter_details){
        // GIVEN - no entries exist
        factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $filter_details = $this->set_test_specific_filters($filter_details);

        $this->assertPostEntriesNotFound($filter_details);
    }

    /**
     * @dataProvider providerPostEntriesFilter
     * @param array $filter_details
     * @throws \Exception
     */
    public function testPostEntries($filter_details){
        // GIVEN
        $generate_entry_count = $this->faker->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        /** @var AccountType $generated_account_type */
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $filter_details = $this->set_test_specific_filters($filter_details);

        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details), true);
        $generated_disabled_entries = $generated_entries->where('disabled', 1);
        if($generated_disabled_entries->count() > 0){   // if there are no disabled entries, then there is no need to do any fancy filtering
            $generated_entries = $generated_entries->sortByDesc('disabled') // sorting so disabled entries are at the start of the collection
                ->splice($generated_disabled_entries->count()-1);
            $generate_entry_count -= $generated_disabled_entries->count();
        }

        if($generate_entry_count < 1){
            // if we only generate entries that have been marked "disabled"
            // then we should create at least one entry is NOT marked "disabled
            $generated_entry = $this->generate_entry_record($generated_account_type->id, false, $this->convert_filters_to_entry_components($filter_details));
            $generated_entries->push($generated_entry);
            $generate_entry_count = 1;
        }

        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, "Filter:".json_encode($filter_details));
        $response_as_array = $response->json();
        $this->assertEquals($generate_entry_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions($generate_entry_count, $response_as_array, $generated_entries, $generated_disabled_entries->pluck('id'));
    }

    /**
     * @dataProvider providerPostEntriesFilter
     * @param array $filter_details
     * @throws \Exception
     */
    public function testPostEntriesByPage($filter_details){
        $page_limit = 3;
        // GIVEN
        $generate_entry_count = $this->faker->numberBetween(($page_limit-1)*self::$MAX_ENTRIES_IN_RESPONSE+1, $page_limit*self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $filter_details = $this->set_test_specific_filters($filter_details);
        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        $entries_in_response = [];
        for($i=0; $i<$page_limit; $i++){
            // WHEN
            $response = $this->json("POST", $this->_uri.'/'.$i, $filter_details);

            // THEN
            $this->assertResponseStatus($response, HttpStatus::HTTP_OK, "Filter:".json_encode($filter_details));
            $response_body_as_array = $response->json();

            $this->assertTrue(is_array($response_body_as_array));
            $this->assertArrayHasKey('count', $response_body_as_array);
            $this->assertEquals($generate_entry_count, $response_body_as_array['count']);
            unset($response_body_as_array['count']);

            if($i+1 == $page_limit){
                $this->assertCount($generate_entry_count-(($page_limit-1)*self::$MAX_ENTRIES_IN_RESPONSE), $response_body_as_array);
            } else {
                $this->assertCount(self::$MAX_ENTRIES_IN_RESPONSE, $response_body_as_array);
            }

            $entries_in_response = array_merge($entries_in_response, $response_body_as_array);
        }

        $this->runEntryListAssertions($generate_entry_count, $entries_in_response, $generated_entries);
    }

    public function testPostEntriesFilterWithMultipleTagIdsAssignedToOneEntry(){
        // GIVEN
        $min_number_of_tags = 2;
        while($this->_generated_tags->count() < $min_number_of_tags){
            $this->_generated_tags = factory(Tag::class, $this->faker->randomDigitNotZero())->create();
        }
        $tag_ids = $this->_generated_tags->pluck('id')->toArray();
        $filter_details[self::$FILTER_KEY_TAGS] = $this->faker->randomElements($tag_ids, $this->faker->numberBetween($min_number_of_tags, count($tag_ids)));
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $generated_entries = $this->batch_generate_entries(1, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertEquals(count($generated_entries), $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions(count($generated_entries), $response_as_array, $generated_entries);
    }

    public function testPostEntriesFilterWithStartDateGreaterThanEndDate(){
        // GIVEN
        $start_date = $this->faker->date();
        do{
            $end_date = $this->faker->date("Y-m-d", $start_date);  // second parameter guarantees $start_date is >= $end_date
        }while($start_date < $end_date);
        $filter_details = [
            self::$FILTER_KEY_START_DATE=>$start_date,
            self::$FILTER_KEY_END_DATE=>$end_date,
        ];

        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $this->batch_generate_entries($this->faker->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE), $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));
        $this->assertPostEntriesNotFound($filter_details);
    }

    public function testPostEntriesFilterWithEndDateGreaterThanStartDate(){
        // GIVEN
        $end_date = $this->faker->date();
        do{
            $start_date = $this->faker->date("Y-m-d", $end_date); // second parameter guarantees $start_date is <= $end_date
        }while($start_date > $end_date);
        $filter_details = [
            self::$FILTER_KEY_START_DATE=>$start_date,
            self::$FILTER_KEY_END_DATE=>$end_date,
        ];

        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $generated_entries_count = $this->faker->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_entries = $this->batch_generate_entries($generated_entries_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertEquals($generated_entries_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions($generated_entries_count, $response_as_array, $generated_entries);
    }

    public function testPostEntriesFilterWithMinValueGreaterThanMaxValue(){
        // GIVEN
        $min_value = $this->faker->randomFloat(2, 0, 50);
        do{
            $max_value = $this->faker->randomFloat(2, 0, $min_value);
        }while($min_value < $max_value);
        $filter_details = [
            self::$FILTER_KEY_MIN_VALUE=>$min_value,
            self::$FILTER_KEY_MAX_VALUE=>$max_value,
        ];

        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $this->batch_generate_entries($this->faker->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE), $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));
        $this->assertPostEntriesNotFound($filter_details);
    }

    public function testPostEntriesFilterWithMaxValueGreaterThanMinValue(){
        // GIVEN
        $max_value = $this->faker->randomFloat(2, 0, 50);
        do{
            $min_value = $this->faker->randomFloat(2, 0, $max_value);
        }while($min_value > $max_value);
        $filter_details = [
            self::$FILTER_KEY_MIN_VALUE=>$min_value,
            self::$FILTER_KEY_MAX_VALUE=>$max_value,
        ];

        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $generated_entries_count = $this->faker->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_entries = $this->batch_generate_entries($generated_entries_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertEquals($generated_entries_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions($generated_entries_count, $response_as_array, $generated_entries);
    }

    public function testPostEntriesFilterSort(){
        // GIVEN
        $generate_entry_count = $this->faker->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id, [], false);
        // how we intend to sort
        $sort_options = Entry::get_fields_required_for_creation();
        unset($sort_options['memo']);   // can't and don't intend to sort by entry memo
        $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_PARAMETER] = $this->faker->randomElement($sort_options);
        $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_DIRECTION] = $this->faker->randomElement([Entry::SORT_DIRECTION_ASC, Entry::SORT_DIRECTION_DESC]);

        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, "Filter:".json_encode($filter_details));
        $response_as_array = $response->json();
        $this->assertEquals($generate_entry_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions(
            $generate_entry_count,
            $response_as_array,
            $generated_entries,
            [],
            $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_PARAMETER],
            $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_DIRECTION]
        );
    }

    /**
     * @param array $filter_details
     */
    private function assertPostEntriesNotFound($filter_details){
        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND, "Filter:".json_encode($filter_details));
        $response_as_array = $response->json();
        $this->assertTrue(is_array($response_as_array));
        $this->assertEmpty($response_as_array);
    }

    /**
     * Because the data provider method is called before the test, we are unlikely to have the same tags setup
     * This method is called at the start of each test and gathers the tags that are available, then assigns them to the "filter" array
     * account_type and account IDs are also randomly selected and assigned to the "filter" array.
     * @param array $filter_details
     * @return array
     */
    private function set_test_specific_filters($filter_details){
        if(key_exists(self::$FILTER_KEY_TAGS, $filter_details)){
            $tag_ids = $this->_generated_tags->pluck('id')->toArray();
            $filter_details[self::$FILTER_KEY_TAGS] = $this->faker->randomElements($tag_ids, $this->faker->numberBetween(1, count($tag_ids)));
        }
        if(key_exists(self::$FILTER_KEY_ACCOUNT_TYPE, $filter_details)){
            $account_types = $this->_generated_account->account_types()->pluck('id')->toArray();
            $filter_details[self::$FILTER_KEY_ACCOUNT_TYPE] = $this->faker->randomElement($account_types);
        }
        if(key_exists(self::$FILTER_KEY_ACCOUNT, $filter_details)){
            $filter_details[self::$FILTER_KEY_ACCOUNT] = $this->_generated_account->id;
        }
        return $filter_details;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function convert_filters_to_entry_components($filters){
        $entry_components = [];
        foreach($filters as $filter_name => $constraint){
            switch($filter_name){
                case self::$FILTER_KEY_START_DATE:
                case self::$FILTER_KEY_END_DATE:
                    $entry_components['entry_date'] = $constraint;
                    break;
                case self::$FILTER_KEY_MIN_VALUE:
                case self::$FILTER_KEY_MAX_VALUE:
                    $entry_components['entry_value'] = $constraint;
                    break;
                case self::$FILTER_KEY_ACCOUNT_TYPE:
                    $entry_components['account_type_id'] = $constraint;
                    break;
                case self::$FILTER_KEY_EXPENSE:
                    if($constraint === true){
                        $entry_components[$filter_name] = 1;
                    } elseif($constraint === false) {
                        $entry_components[$filter_name] = 0;
                    }
                    break;
                case self::$FILTER_KEY_UNCONFIRMED:
                    if($constraint === true){
                        $entry_components['confirm'] = 0;
                    }
                    break;
                case self::$FILTER_KEY_ATTACHMENTS:
                    $entry_components['has_attachments'] = $constraint;
                    break;
                case self::$FILTER_KEY_TAGS:
                    $entry_components[$filter_name] = is_array($constraint) ? $constraint : [$constraint];
                    break;
                case self::$FILTER_KEY_IS_TRANSFER:
                    if($constraint == true){
                        $entry_components['transfer_entry_id'] = $this->faker->randomDigitNotZero();
                    } elseif($constraint == false) {
                        $entry_components['transfer_entry_id'] = null;
                    }
                    break;
            }
        }
        return $entry_components;
    }

}