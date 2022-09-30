<?php

namespace Tests\Feature\Api\Get;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Attachment;
use App\Models\Entry;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class GetEntryTest extends TestCase {

    use WithFaker;

    private int $_generate_tag_count;
    private int $_generate_attachment_count;
    private string $_base_uri = '/api/entry/%d';

    public function setUp(): void{
        parent::setUp();

        $this->_generate_attachment_count = $this->faker->randomDigitNotZero();
        $this->_generate_tag_count = $this->faker->randomDigitNotZero();
    }

    public function testGetEntryWithNoData(){
        // GIVEN - no data in database
        $entry_id = 99999;

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $entry_id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetEntryData(){
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->create(['account_id'=>$generated_account->id]);
        $generated_entry = Entry::factory()->create(['account_type_id'=>$generated_account_type->id]);
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryDataWithRelatedTransferEntry(){
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->create(['account_id'=>$generated_account->id]);
        $generated_transfer_entry = Entry::factory()->create(['account_type_id'=>$generated_account_type->id]);
        $generated_entry = Entry::factory()->create(['account_type_id'=>$generated_account_type->id, 'transfer_entry_id'=>$generated_transfer_entry->id]);
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryDataWithNoAssociatedTags(){
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->create(['account_id'=>$generated_account->id]);
        $generated_entry = Entry::factory()->create(['account_type_id'=>$generated_account_type->id]);
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['tags']));
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryWithNoAssociatedAttachments(){
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->create(['account_id'=>$generated_account->id]);
        $generated_entry = Entry::factory()->create(['account_type_id'=>$generated_account_type->id]);
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertTrue(is_array($response_body_as_array['attachments']));
        $this->assertEmpty($response_body_as_array['attachments']);
    }

    public function testGetEntryWithNoAssociatedTagsAndAttachments(){
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->create(['account_id'=>$generated_account->id]);
        $generated_entry = Entry::factory()->create(['account_type_id'=>$generated_account_type->id]);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertParentNodesExist($response_body_as_array);
        $this->assertEntryNodeValuesExcludingRelationshipsOK($generated_entry, $response_body_as_array);
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertTrue(is_array($response_body_as_array['attachments']));
        $this->assertEmpty($response_body_as_array['attachments']);
    }

    public function testGetEntryThatIsMarkedDisabled(){
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->create(['account_id'=>$generated_account->id]);
        $generated_entry = Entry::factory()->create(['disabled'=>1, 'account_type_id'=>$generated_account_type->id]);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    /**
     * @param int $generated_entry_id
     * @return array
     */
    private function generateAttachmentsAndOutputAsArray(int $generated_entry_id){
        $generated_attachments = Attachment::factory()->count($this->_generate_attachment_count)->create(['entry_id'=>$generated_entry_id]);
        $generated_attachments_as_array = [];
        $generated_attachment_i = 0;
        foreach($generated_attachments as $generated_attachment){
            $generated_attachments_as_array[$generated_attachment_i]['uuid'] = $generated_attachment->uuid;
            $generated_attachments_as_array[$generated_attachment_i]['name'] = $generated_attachment->name;
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
        $generated_tags = Tag::factory()->count($this->_generate_tag_count)->create();
        $generated_tags_as_array = [];
        $generated_tag_i = 0;
        foreach($generated_tags as $generated_tag){
            $generated_entry->tags()->attach($generated_tag->id);
            $generated_tags_as_array[$generated_tag_i]['id'] = $generated_tag->id;
            $generated_tags_as_array[$generated_tag_i]['name'] = $generated_tag->name;
            $generated_tag_i++;
        }
        return $generated_tags_as_array;
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
        $this->assertArrayHasKey('account_type_id', $entry_nodes);
        $this->assertArrayHasKey("transfer_entry_id", $entry_nodes);
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
        $failure_message = 'generated entry:'.json_encode($generated_entry)."\nresponse entry:".json_encode($response_body_as_array);
        $this->assertEquals($generated_entry->id, $response_body_as_array['id'], $failure_message);
        $this->assertEquals($generated_entry->entry_date, $response_body_as_array['entry_date'], $failure_message);
        $this->assertEquals($generated_entry->entry_value, $response_body_as_array['entry_value'], $failure_message);
        $this->assertEquals($generated_entry->memo, $response_body_as_array['memo'], $failure_message);
        $this->assertEquals($generated_entry->expense, $response_body_as_array['expense'], $failure_message);
        $this->assertEquals($generated_entry->confirm, $response_body_as_array['confirm'], $failure_message);
        $this->assertEquals($generated_entry->account_type_id, $response_body_as_array['account_type_id'], $failure_message);
        $this->assertEquals($generated_entry->transfer_entry_id, $response_body_as_array['transfer_entry_id'], $failure_message);
        $this->assertDateFormat($response_body_as_array['create_stamp'], Carbon::ATOM, $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertDateFormat($response_body_as_array['modified_stamp'], Carbon::ATOM, $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertDatetimeWithinOneSecond($generated_entry->create_stamp, $response_body_as_array['create_stamp'], $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertDatetimeWithinOneSecond($generated_entry->modified_stamp, $response_body_as_array['modified_stamp'], $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
    }

    /**
     * @param $entry_tags_node
     * @param array $generated_tags_as_array
     */
    private function assertEntryTagsNodeOK($entry_tags_node, $generated_tags_as_array){
        $this->assertTrue(is_array($entry_tags_node));
        $this->assertCount($this->_generate_tag_count, $entry_tags_node);
        foreach($entry_tags_node as $tag_in_response){
            $this->assertArrayHasKey('id', $tag_in_response);
            $this->assertArrayHasKey('name', $tag_in_response);
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
        $this->assertCount($this->_generate_attachment_count, $entry_attachments_node);
        foreach($entry_attachments_node as $attachment_in_response){
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            $this->assertArrayHasKey('name', $attachment_in_response);
            $this->assertArrayHasKey('stamp', $attachment_in_response);
            $this->assertTrue(
                in_array($attachment_in_response, $generated_attachments_as_array),
                "attachment in response:".json_encode($attachment_in_response)."\ngenerated attachments:".json_encode($generated_attachments_as_array)
            );
        }
    }

}