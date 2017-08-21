<?php

namespace Tests\Feature\Api;

use App\AccountType;
use App\Entry;
use App\Http\Controllers\Api\EntryController;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class GetEntriesTest extends ListEntriesBase {

    public function testGetEntriesThatDoNotExist(){
        // GIVEN - no data in database

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $this->getResponseAsArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetEntries(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);

        $generate_entry_count = $this->_faker->numberBetween(4, 50);
        $generated_entries = [];
        $generated_disabled_entries = [];
        for($i=0; $i<$generate_entry_count; $i++){
            $entry_disabled = $this->_faker->boolean;
            $generated_entry = $this->generate_entry_record($generated_account_type->id, $entry_disabled);

            if($entry_disabled){
                $generated_disabled_entries[] = $generated_entry->id;
            } else {
                $generated_entries[] = $generated_entry;
            }
        }
        $generate_entry_count -= count($generated_disabled_entries);
        if($generate_entry_count == 0){
            // do this in case we ever generated nothing but "disabled" entries
            $this->generate_entry_record($generated_account_type->id, false);
            $generate_entry_count++;
        }

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $this->getResponseAsArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($generate_entry_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);

        $this->runEntryListAssertions($generate_entry_count, $response_body_as_array, $generated_entries, $generated_disabled_entries);
    }

    public function testGetEntriesByPage(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create(['account_id' => $this->_generated_account->id]);
        $generate_entry_count = $this->_faker->numberBetween(101, 150);
        $generated_entries = [];
        for($i = 0; $i < $generate_entry_count; $i++){
            $generated_entries[] = $this->generate_entry_record($generated_account_type->id, false);
        }

        $entries_in_response = [];
        for($i=0; $i<3; $i++){
            // WHEN
            $response = $this->get($this->_uri.'/'.$i);

            // THEN
            $response->assertStatus(HttpStatus::HTTP_OK);
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

    public function providerLargeDataSets(){
        return [
            '200 entry records'=>[200],
            '500 entry records'=>[500],
            '1000 entry records'=>[1000],
            '2000 entry records'=>[2000],
            '5000 entry records'=>[5000],
            '10000 entry records'=>[10000],
            '15000 entry records'=>[15000],
            '20000 entry records'=>[20000],
            '25000 entry records'=>[25000],
        ];
    }

    /**
     * @dataProvider providerLargeDataSets
     * @param int $entry_count
     */
    public function testLargeDataSets($entry_count){
        // GIVEN
        $table = with(new Entry)->getTable();
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $generated_entries = factory(Entry::class, EntryController::MAX_ENTRIES_IN_RESPONSE)->make(['account_type'=>$generated_account_type->id]);
        // generating entries in batches and using database insert methods because it's faster
        for($i=0; $i<($entry_count/EntryController::MAX_ENTRIES_IN_RESPONSE); $i++){
            DB::table($table)->insert($generated_entries->toArray());
        }

        // WHEN
        $response = $this->get($this->_uri);
        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK); // this is the MOST IMPORTANT check. it confirms we were able to handle a large data set

        $response_as_array = $this->getResponseAsArray($response);
        $this->assertEquals($entry_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->assertCount(EntryController::MAX_ENTRIES_IN_RESPONSE, $response_as_array);
    }

}