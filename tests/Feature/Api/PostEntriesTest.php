<?php

namespace Tests\Feature\Api;

use App\AccountType;
use App\Http\Controllers\Api\EntryController;
use Symfony\Component\HttpFoundation\Response;

class PostEntriesTest extends ListEntriesBase {

    public function providerPostEntriesFilter(){
        // need to call setUp() before running through a data provider method
        // environment needs setting up and isn't until setUp() is called
        $this->setUp();

        $filter = [];
        $filter['no filter'] = [[]];
        $filter["filtering 'expense' & 'income'"] = [['income'=>true, 'expense'=>true]];

        $start_date = $this->_faker->date();
        do{
            $end_date = $this->_faker->date();
        }while($start_date > $end_date);
        $min_value = $this->_faker->randomFloat(2, 0, 50);
        do{
            $max_value = $this->_faker->randomFloat(2, 0, 50);
        }while($min_value > $max_value);

        $filter_details = [
            'start_date'=>$start_date,
            'end_date'=>$end_date,
            'account'=>0,       // will be set later
            'account_type'=>0,  // will be set later
            'tags'=>[],         // will be set later
            'expense'=>$this->_faker->boolean,
            'attachments'=>$this->_faker->boolean,
            'min_value'=>$min_value,
            'max_value'=>$max_value,
            'unconfirmed'=>$this->_faker->boolean
        ];

        // confirm all filters in EntryController are listed here
        $current_filters = EntryController::get_filter_details();
        foreach(array_keys($current_filters) as $existing_filter){
            if(strpos('.*', $existing_filter) === false){
                continue;
            }
            $this->assertArrayHasKey($existing_filter, $filter_details);
        }

        // individual filter requests
        foreach($filter_details as $filter_name=>$filter_value){
            // confirm all filters listed in test are in EntryController
            $this->assertArrayHasKey($filter_name, $current_filters);

            // adding a switch to catch all eventualities for boolean conditions
            switch($filter_name){
                case 'expense':
                case 'attachments':
                case 'unconfirmed':
                    $filter["filtering '".$filter_name.":true'"] = [
                        [$filter_name=>true]
                    ];
                    $filter["filtering '".$filter_name.":false'"] = [
                        [$filter_name=>false]
                    ];
                    break;
                default:
                    $filter["filtering '".$filter_name."'"] = [
                        [$filter_name=>$filter_value]
                    ];
            }
        }

        // batch of filter requests
        $batched_filter_details = array_rand($filter_details, 3);
        $filter["filtering '".implode("','", $batched_filter_details)."'"] = [array_intersect_key($filter_details, array_flip($batched_filter_details))];

        // all filter requests
        $filter["filtering '".implode("','", array_keys($filter_details))."'"] = [$filter_details];


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
     */
    public function testPostEntries($filter_details){
        // GIVEN
        $generate_entry_count = $this->_faker->numberBetween(4, 50);
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $filter_details = $this->set_test_specific_filters($filter_details);

        $generated_entries = [];
        $generated_disabled_entries = [];
        for($i=0; $i<$generate_entry_count; $i++){
            $entry_disabled = $this->_faker->boolean;
            $generated_entry = $this->generate_entry_record(
                $generated_account_type->id,
                $entry_disabled,
                $this->convert_filters_to_entry_components($filter_details)
            );

            if($entry_disabled){
                $generated_disabled_entries[] = $generated_entry->id;
            } else {
                $generated_entries[] = $generated_entry;
            }
        }
        $generate_entry_count -= count($generated_disabled_entries);
        if($generate_entry_count == 0){
            // do this in case we ever generated nothing but "disabled" entries
            $generated_entries[] = $this->generate_entry_record($generated_account_type->id, false, $this->convert_filters_to_entry_components($filter_details));
            $generate_entry_count++;
        }

        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_as_array = $this->getResponseAsArray($response);
        $this->assertEquals($generate_entry_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions($generate_entry_count, $response_as_array, $generated_entries, $generated_disabled_entries);
    }

    /**
     * @dataProvider providerPostEntriesFilter
     * @param $filter_details
     */
    public function testPostEntriesByPage($filter_details){
        // GIVEN
        $generate_entry_count = $this->_faker->numberBetween(101, 150);
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $filter_details = $this->set_test_specific_filters($filter_details);
        $generated_entries = $this->batch_generate_non_disabled_entries($generate_entry_count, $generated_account_type->id, $filter_details);

        $entries_in_response = [];
        for($i=0; $i<3; $i++){
            // WHEN
            $response = $this->json("POST", $this->_uri.'/'.$i, $filter_details);

            // THEN
            $response->assertStatus(Response::HTTP_OK);
            $response_body_as_array = $this->getResponseAsArray($response);

            $this->assertTrue(is_array($response_body_as_array));
            $this->assertArrayHasKey('count', $response_body_as_array);
            $this->assertEquals($generate_entry_count, $response_body_as_array['count']);
            unset($response_body_as_array['count']);

            if($i+1 == 3){
                $this->assertEquals($generate_entry_count-(2*EntryController::MAX_ENTRIES_IN_RESPONSE), count($response_body_as_array));
            } else {
                $this->assertEquals(EntryController::MAX_ENTRIES_IN_RESPONSE, count($response_body_as_array));
            }

            $entries_in_response = array_merge($entries_in_response, $response_body_as_array);
        }

        $this->runEntryListAssertions($generate_entry_count, $entries_in_response, $generated_entries);
    }

    public function testPostEntriesFilterWithStartDateGreaterThanEndDate(){
        // GIVEN
        $start_date = $this->_faker->date();
        do{
            $end_date = $this->_faker->date();
        }while($start_date < $end_date);
        $filter_details = [
            'start_date'=>$start_date,
            'end_date'=>$end_date,
        ];

        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $this->batch_generate_non_disabled_entries($this->_faker->numberBetween(4, 50), $generated_account_type->id, $filter_details);
        $this->assertPostEntriesNotFound($filter_details);
    }

    public function testPostEntriesFilterWithMinValueGreaterThanMaxValue(){
        // GIVEN
        $min_value = $this->_faker->randomFloat(2, 0, 50);
        do{
            $max_value = $this->_faker->randomFloat(2, 0, 50);
        }while($min_value < $max_value);
        $filter_details = [
            'min_value'=>$min_value,
            'max_value'=>$max_value
        ];

        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $this->batch_generate_non_disabled_entries($this->_faker->numberBetween(4, 50), $generated_account_type->id, $filter_details);
        $this->assertPostEntriesNotFound($filter_details);
    }

    /**
     * @param array $filter_details
     */
    private function assertPostEntriesNotFound($filter_details){
        // WHEN
        $response = $this->json("POST", $this->_uri, $filter_details);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_as_array = $this->getResponseAsArray($response);
        $this->assertTrue(is_array($response_as_array));
        $this->assertEmpty($response_as_array);
    }

    /**
     * Because the data provider method is called before the test, we are unlikely to have the same tags setup
     * This method is called at the start of each test and gathers the tags that are available, then assigns them to the "filter" array
     * @param array $filter_details
     * @return array
     */
    private function set_test_specific_filters($filter_details){
        if(key_exists('tags', $filter_details)){
            $tag_ids = $this->_generated_tags->pluck('id')->toArray();
            $filter_details['tags'] = $this->_faker->randomElements($tag_ids, $this->_faker->numberBetween(1, count($tag_ids)));
        }
        if(key_exists('account_type', $filter_details)){
            $account_types = $this->_generated_account->account_types()->pluck('id')->toArray();
            $filter_details['account_type'] = $this->_faker->randomElement($account_types);
        }
        if(key_exists('account', $filter_details)){
            $filter_details['account'] = $this->_generated_account->id;
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
                case 'start_date':
                case 'end_date':
                    $entry_components['entry_date'] = $constraint;
                    break;
                case 'min_value':
                case 'max_value':
                    $entry_components['entry_value'] = $constraint;
                    break;
                case 'account_type':
                    $entry_components[$filter_name] = $constraint;
                    break;
                case 'expense':
                    if($constraint == true){
                        $entry_components[$filter_name] = 1;
                    } elseif($constraint == false) {
                        $entry_components[$filter_name] = 0;
                    }
                    break;
                case 'unconfirmed':
                    if($constraint == true){
                        $entry_components['confirm'] = 0;
                    }
                    break;
                case 'attachments':
                    if($constraint == true){
                        $entry_components['has_attachments'] = $constraint;
                    }
                    break;
                case 'tags':
                    $entry_components[$filter_name] = [$this->_faker->randomElement($constraint)];
                    break;
            }
        }
        return $entry_components;
    }

    /**
     * @param int $generate_entry_count
     * @param int $generated_account_type_id
     * @param array $filter_details
     * @return array
     */
    private function batch_generate_non_disabled_entries($generate_entry_count, $generated_account_type_id, $filter_details){
        $generated_entries = [];
        for($i=0; $i<$generate_entry_count; $i++){
            $generated_entries[] = $this->generate_entry_record(
                $generated_account_type_id,
                false,
                $this->convert_filters_to_entry_components($filter_details)
            );
        }
        return $generated_entries;
    }

}