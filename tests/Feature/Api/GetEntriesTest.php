<?php

namespace Tests\Feature\Api;

use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Tag;

use Illuminate\Database\Eloquent\Collection;
use Faker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

class GetEntriesTest extends TestCase {

    use DatabaseMigrations;

    private $_uri = '/api/entries';

    public function testGetEntriesThatDoNotExist(){
        // GIVEN - no data in database

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }


    public function testGetEntries(){
        $faker = Faker\Factory::create();
        // GIVEN
        $generate_tag_count = $faker->randomDigitNotNull;
        $generated_tags = factory(Tag::class, $generate_tag_count)->create();
        $generated_account_type_count = $faker->randomDigitNotNull;
        $generated_account_types = factory(AccountType::class, $generated_account_type_count)->create();

        do{
            $generate_entry_count = $faker->randomDigitNotNull;
        } while($generate_entry_count < 4);
        $generated_entries = [];
        $generated_deleted_entries = [];
        for($i=0; $i<$generate_entry_count; $i++){
            $entry_deleted = $faker->boolean;
            $generated_entry = $this->generate_entry_record($faker, $generated_account_types, $entry_deleted, $generate_tag_count, $generated_tags);

            if($entry_deleted){
                $generated_deleted_entries[] = $generated_entry->id;
            } else {
                $generated_entries[] = $generated_entry;
            }
        }
        $generate_entry_count -= count($generated_deleted_entries);
        if($generate_entry_count == 0){
            // do this in case we ever generated nothing but "deleted" entries
            $this->generate_entry_record($faker, $generated_account_types, false, $generate_tag_count, $generated_tags);
            $generate_entry_count++;
        }

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($generate_entry_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);
        $this->assertEquals($generate_entry_count, count($response_body_as_array));

        foreach($response_body_as_array as $entry_in_response){
            $generated_entry = null;
            $this->assertArrayHasKey('id', $entry_in_response);
            $this->assertNotContains($entry_in_response['id'], $generated_deleted_entries);
            foreach($generated_entries as $generated_entry){
                if($entry_in_response['id'] == $generated_entry->id){
                    break;
                }
                $generated_entry = null;
            }
            $this->assertNotNull($generated_entry);
            $this->assertEntryNodesExist($entry_in_response);
            $this->assertEntryNodesMatchGeneratedEntry($entry_in_response, $generated_entry);
        }
    }

    /**
     * @param Faker\Generator $faker
     * @param Collection $account_types_collection
     * @param bool $entry_deleted
     * @param int $tag_count
     * @param Collection $tags_collection
     * @return Entry
     */
    private function generate_entry_record($faker, $account_types_collection, $entry_deleted, $tag_count, $tags_collection){
        $randomly_selected_account_type = $account_types_collection->random();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$randomly_selected_account_type->id, 'deleted'=>$entry_deleted]);
        $generate_attachment_count = $faker->randomDigit;
        factory(Attachment::class, $generate_attachment_count)->create(['entry_id'=>$generated_entry->id]);
        do{
            $assign_tag_to_entry_count = $faker->randomDigit;
        } while($assign_tag_to_entry_count > $tag_count);
        for($j=0; $j<$assign_tag_to_entry_count; $j++){
            $randomly_selected_tag = $tags_collection->random();
            $generated_entry->tags()->attach($randomly_selected_tag->id);
        }

        return $generated_entry;
    }

    /**
     * @param array $entry_nodes
     */
    private function assertEntryNodesExist($entry_nodes){
        $this->assertArrayHasKey('entry_date', $entry_nodes);
        $this->assertArrayHasKey('entry_value', $entry_nodes);
        $this->assertArrayHasKey('memo', $entry_nodes);
        $this->assertArrayHasKey('account_type', $entry_nodes);
        $this->assertArrayHasKey('expense', $entry_nodes);
        $this->assertArrayHasKey('confirm', $entry_nodes);
        $this->assertArrayHasKey('create_stamp', $entry_nodes);
        $this->assertArrayHasKey('modified_stamp', $entry_nodes);
        $this->assertArrayHasKey('has_attachments', $entry_nodes);
        $this->assertArrayHasKey('tags', $entry_nodes);
    }

    /**
     * @param array $entry_nodes
     * @param Entry $generated_entry
     */
    private function assertEntryNodesMatchGeneratedEntry($entry_nodes, $generated_entry){
        $this->assertEquals($generated_entry->entry_date, $entry_nodes['entry_date']);
        $this->assertEquals($generated_entry->entry_value, $entry_nodes['entry_value']);
        $this->assertEquals($generated_entry->memo, $entry_nodes['memo']);
        $this->assertEquals($generated_entry->account_type, $entry_nodes['account_type']);
        $this->assertEquals($generated_entry->expense, $entry_nodes['expense']);
        $this->assertEquals($generated_entry->confirm, $entry_nodes['confirm']);
        $this->assertEquals($generated_entry->create_stamp, $entry_nodes['create_stamp']);
        $this->assertEquals($generated_entry->modified_stamp, $entry_nodes['modified_stamp']);
        $this->assertTrue(is_bool($entry_nodes['has_attachments']));
        $this->assertEquals($generated_entry->has_attachments(), $entry_nodes['has_attachments']);
        $this->assertTrue(is_array($entry_nodes['tags']));
        $this->assertEquals($generated_entry->get_tag_ids(), $entry_nodes['tags']);
    }

}