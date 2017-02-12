<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker;

use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Tag;

class GetEntryTest extends TestCase {

    use DatabaseMigrations;

    private $_generate_tag_count;
    private $_generate_attachment_count;

    public function setUp(){
        parent::setUp();

        $faker = Faker\Factory::create();
        $this->_generate_attachment_count = $faker->randomNumber(1);
        $this->_generate_tag_count = $faker->randomNumber(1);
    }

    public function testGetEntryWithNoData(){
        // GIVEN - no data in database
        $entry_id = 99999;

        // WHEN
        $response = $this->get('/api/entry/'.$entry_id);

        // THEN
        $response->assertStatus(404);
        $response_body_as_array = $this->convertResponseToArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetEntryData(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(200);
        $response_body_as_array = $this->convertResponseToArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryDataWithNoAssociatedTags(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(200);
        $response_body_as_array = $this->convertResponseToArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['tags']));
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryWithNoAssociatedAttachments(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(200);
        $response_body_as_array = $this->convertResponseToArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertTrue(is_array($response_body_as_array['attachments']));
        $this->assertEmpty($response_body_as_array['attachments']);
    }

    public function testGetEntryWithNoAssociatedTagsAndAttachments(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(200);
        $response_body_as_array = $this->convertResponseToArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertTrue(is_array($response_body_as_array['attachments']));
        $this->assertEmpty($response_body_as_array['attachments']);
    }

    public function testGetEntryThatIsMarkedDeleted(){
        // GIVEN
        $generated_entry = factory(Entry::class)->create(['deleted'=>1]);

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(404);
        $response_body_as_array = $this->convertResponseToArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    /**
     * @param int $generated_entry_id
     * @return array
     */
    private function generateAttachmentsAndOutputAsArray($generated_entry_id){
        $generated_attachments = factory(Attachment::class, $this->_generate_attachment_count)->create(['entry_id'=>$generated_entry_id]);
        $generated_attachments_as_array = [];
        $generated_attachment_i = 0;
        foreach($generated_attachments as $generated_attachment){
            $generated_attachments_as_array[$generated_attachment_i]['uuid'] = $generated_attachment->uuid;
            $generated_attachments_as_array[$generated_attachment_i]['attachment'] = $generated_attachment->attachment;
            $generated_attachments_as_array[$generated_attachment_i]['stamp'] = $generated_attachment->stamp;
            $generated_attachment_i++;
        }
        return $generated_attachments_as_array;
    }

    /**
     * @param Entry $generated_entry
     * @return array
     */
    private function generateTagsAndOutputAsArray($generated_entry){
        $generated_tags = factory(Tag::class, $this->_generate_tag_count)->create();
        $generated_tags_as_array = [];
        $generated_tag_i = 0;
        foreach($generated_tags as $generated_tag){
            $generated_entry->tags()->attach($generated_tag->id);
            $generated_tags_as_array[$generated_tag_i]['id'] = $generated_tag->id;
            $generated_tags_as_array[$generated_tag_i]['tag'] = $generated_tag->tag;
            $generated_tag_i++;
        }
        return $generated_tags_as_array;
    }

    /**
     * @param \Illuminate\Foundation\Testing\TestResponse $response
     * @return array
     */
    private function convertResponseToArray($response){
        $response_body = $response->getContent();
        return json_decode($response_body, true);
    }

    /**
     * @param array $entry_nodes
     */
    private function assertParentNodesExist($entry_nodes){
        $this->assertArrayHasKey('id', $entry_nodes);
        $this->assertArrayHasKey('entry_date', $entry_nodes);
        $this->assertArrayHasKey('entry_value', $entry_nodes);
        $this->assertArrayHasKey('memo', $entry_nodes);
        $this->assertArrayHasKey('expense', $entry_nodes);
        $this->assertArrayHasKey('confirm', $entry_nodes);
        $this->assertArrayHasKey('account_type', $entry_nodes);
        $this->assertArrayHasKey('create_stamp', $entry_nodes);
        $this->assertArrayHasKey('modified_stamp', $entry_nodes);
        $this->assertArrayHasKey('tags', $entry_nodes);
        $this->assertArrayHasKey('attachments', $entry_nodes);
    }

    /**
     * @param Entry $generated_entry
     * @param array $response_body_as_array
     */
    private function assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array){
        $this->assertEquals($generated_entry->id, $response_body_as_array['id']);
        $this->assertEquals($generated_entry->entry_date, $response_body_as_array['entry_date']);
        $this->assertEquals($generated_entry->entry_value, $response_body_as_array['entry_value']);
        $this->assertEquals($generated_entry->memo, $response_body_as_array['memo']);
        $this->assertEquals($generated_entry->expense, $response_body_as_array['expense']);
        $this->assertEquals($generated_entry->confirm, $response_body_as_array['confirm']);
        $this->assertEquals($generated_entry->account_type, $response_body_as_array['account_type']);
        $this->assertEquals($generated_entry->create_stamp, $response_body_as_array['create_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertEquals($generated_entry->modified_stamp, $response_body_as_array['modified_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
    }

    /**
     * @param $entry_tags_node
     * @param array $generated_tags_as_array
     */
    private function assertEntryTagsNodeOK($entry_tags_node, $generated_tags_as_array){
        $this->assertTrue(is_array($entry_tags_node));
        $this->assertEquals($this->_generate_tag_count, count($entry_tags_node));
        foreach($entry_tags_node as $tag_in_response){
            $this->assertArrayHasKey('id', $tag_in_response);
            $this->assertArrayHasKey('tag', $tag_in_response);
            $this->assertTrue(
                in_array($tag_in_response, $generated_tags_as_array),
                "tag in response:".json_encode($tag_in_response)."\ngenerated tags:".json_encode($generated_tags_as_array)
            );
        }
    }

    /**
     * @param $entry_attachments_node
     * @param array $generated_attachments_as_array
     */
    private function assertEntryAttachmentsNodeOK($entry_attachments_node, $generated_attachments_as_array){
        $this->assertTrue(is_array($entry_attachments_node));
        $this->assertEquals($this->_generate_attachment_count, count($entry_attachments_node));
        foreach($entry_attachments_node as $attachment_in_response){
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            $this->assertArrayHasKey('attachment', $attachment_in_response);
            $this->assertArrayHasKey('stamp', $attachment_in_response);
            $this->assertTrue(
                in_array($attachment_in_response, $generated_attachments_as_array),
                "attachment in response:".json_encode($attachment_in_response)."\ngenerated attachments:".json_encode($generated_attachments_as_array)
            );
        }
    }

}