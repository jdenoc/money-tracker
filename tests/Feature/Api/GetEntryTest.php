<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Tag;

class GetEntryTest extends TestCase {

    use DatabaseMigrations;

    public function testGetEntryWithNoData(){
        // GIVEN - no data in database
        $entry_id = 99999;

        // WHEN
        $response = $this->get('/api/entry/'.$entry_id);

        // THEN
        $response->assertStatus(404);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetEntryData(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);
        $generated_tag_count = 3;
        $generated_tags = factory(Tag::class, $generated_tag_count)->create();
        $generated_tags_as_array = [];
        $generated_tag_i = 0;
        foreach($generated_tags as $generated_tag){
            $generated_entry->tags()->attach($generated_tag->id);
            $generated_tags_as_array[$generated_tag_i]['id'] = $generated_tag->id;
            $generated_tags_as_array[$generated_tag_i]['tag'] = $generated_tag->tag;
            $generated_tag_i++;
        }
        $generated_attachment_count = 2;
        $generated_attachments = factory(Attachment::class, $generated_attachment_count)->create(['entry_id'=>$generated_entry->id]);
        $generated_attachments_as_array = [];
        $generated_attachment_i = 0;
        foreach($generated_attachments as $generated_attachment){
            $generated_attachments_as_array[$generated_attachment_i]['uuid'] = $generated_attachment->uuid;
            $generated_attachments_as_array[$generated_attachment_i]['attachment'] = $generated_attachment->attachment;
            $generated_attachments_as_array[$generated_attachment_i]['stamp'] = $generated_attachment->stamp;
            $generated_attachment_i++;
        }

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($generated_entry->id, $response_body_as_array['id']);
        $this->assertArrayHasKey('entry_date', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_date, $response_body_as_array['entry_date']);
        $this->assertArrayHasKey('entry_value', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_value, $response_body_as_array['entry_value']);
        $this->assertArrayHasKey('memo', $response_body_as_array);
        $this->assertEquals($generated_entry->memo, $response_body_as_array['memo']);
        $this->assertArrayHasKey('expense', $response_body_as_array);
        $this->assertEquals($generated_entry->expense, $response_body_as_array['expense']);
        $this->assertArrayHasKey('confirm', $response_body_as_array);
        $this->assertEquals($generated_entry->confirm, $response_body_as_array['confirm']);
        $this->assertArrayHasKey('create_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->create_stamp, $response_body_as_array['create_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('modified_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->modified_stamp, $response_body_as_array['modified_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('account_type', $response_body_as_array);
        $this->assertEquals($generated_entry->account_type, $response_body_as_array['account_type']);
        $this->assertArrayHasKey('tags', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['tags']));
        $this->assertEquals($generated_tag_count, count($response_body_as_array['tags']));
        foreach($response_body_as_array['tags'] as $tag_in_response){
            $this->assertArrayHasKey('id', $tag_in_response);
            $this->assertArrayHasKey('tag', $tag_in_response);
            $this->assertTrue(in_array($tag_in_response, $generated_tags_as_array));
        }
        $this->assertArrayHasKey('attachments', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['attachments']));
        $this->assertEquals($generated_attachment_count, count($response_body_as_array['attachments']));
        foreach($response_body_as_array['attachments'] as $attachment_in_response){
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            $this->assertArrayHasKey('attachment', $attachment_in_response);
            $this->assertArrayHasKey('stamp', $attachment_in_response);
            $this->assertTrue(
                in_array($attachment_in_response, $generated_attachments_as_array),
                "attachment in response:".json_encode($attachment_in_response)."\ngenerated attachments:".json_encode($generated_attachments_as_array)
            );
        }
    }

    public function testGetEntryDataWithNoAssociatedTags(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);
        $generated_attachment_count = 2;
        $generated_attachments = factory(Attachment::class, $generated_attachment_count)->create(['entry_id'=>$generated_entry->id]);
        $generated_attachments_as_array = [];
        $generated_attachment_i = 0;
        foreach($generated_attachments as $generated_attachment){
            $generated_attachments_as_array[$generated_attachment_i]['uuid'] = $generated_attachment->uuid;
            $generated_attachments_as_array[$generated_attachment_i]['attachment'] = $generated_attachment->attachment;
            $generated_attachments_as_array[$generated_attachment_i]['stamp'] = $generated_attachment->stamp;
            $generated_attachment_i++;
        }

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));

        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($generated_entry->id, $response_body_as_array['id']);
        $this->assertArrayHasKey('entry_date', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_date, $response_body_as_array['entry_date']);
        $this->assertArrayHasKey('entry_value', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_value, $response_body_as_array['entry_value']);
        $this->assertArrayHasKey('memo', $response_body_as_array);
        $this->assertEquals($generated_entry->memo, $response_body_as_array['memo']);
        $this->assertArrayHasKey('expense', $response_body_as_array);
        $this->assertEquals($generated_entry->expense, $response_body_as_array['expense']);
        $this->assertArrayHasKey('confirm', $response_body_as_array);
        $this->assertEquals($generated_entry->confirm, $response_body_as_array['confirm']);
        $this->assertArrayHasKey('account_type', $response_body_as_array);
        $this->assertEquals($generated_entry->account_type, $response_body_as_array['account_type']);
        $this->assertArrayHasKey('create_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->create_stamp, $response_body_as_array['create_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('modified_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->modified_stamp, $response_body_as_array['modified_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('tags', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['tags']));
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertArrayHasKey('attachments', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['attachments']));
        $this->assertEquals($generated_attachment_count, count($response_body_as_array['attachments']));
        foreach($response_body_as_array['attachments'] as $attachment_in_response){
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            $this->assertArrayHasKey('attachment', $attachment_in_response);
            $this->assertArrayHasKey('stamp', $attachment_in_response);
            $this->assertTrue(
                in_array($attachment_in_response, $generated_attachments_as_array),
                "attachment in response:".json_encode($attachment_in_response)."\ngenerated attachments:".json_encode($generated_attachments_as_array)
            );
        }
    }

    public function testGetEntryWithNoAssociatedAttachments(){
        // GIVEN
        $generated_account_type = factory(AccountType::class)->create();
        $generated_entry = factory(Entry::class)->create(['account_type'=>$generated_account_type->id]);
        $generated_tag_count = 3;
        $generated_tags = factory(Tag::class, $generated_tag_count)->create();
        $generated_tags_as_array = [];
        $generated_tag_i = 0;
        foreach($generated_tags as $generated_tag){
            $generated_entry->tags()->attach($generated_tag->id);
            $generated_tags_as_array[$generated_tag_i]['id'] = $generated_tag->id;
            $generated_tags_as_array[$generated_tag_i]['tag'] = $generated_tag->tag;
            $generated_tag_i++;
        }

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($generated_entry->id, $response_body_as_array['id']);
        $this->assertArrayHasKey('entry_date', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_date, $response_body_as_array['entry_date']);
        $this->assertArrayHasKey('entry_value', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_value, $response_body_as_array['entry_value']);
        $this->assertArrayHasKey('memo', $response_body_as_array);
        $this->assertEquals($generated_entry->memo, $response_body_as_array['memo']);
        $this->assertArrayHasKey('expense', $response_body_as_array);
        $this->assertEquals($generated_entry->expense, $response_body_as_array['expense']);
        $this->assertArrayHasKey('confirm', $response_body_as_array);
        $this->assertEquals($generated_entry->confirm, $response_body_as_array['confirm']);
        $this->assertArrayHasKey('create_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->create_stamp, $response_body_as_array['create_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('modified_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->modified_stamp, $response_body_as_array['modified_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('account_type', $response_body_as_array);
        $this->assertEquals($generated_entry->account_type, $response_body_as_array['account_type']);
        $this->assertArrayHasKey('tags', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['tags']));
        $this->assertEquals($generated_tag_count, count($response_body_as_array['tags']));
        foreach($response_body_as_array['tags'] as $tag_in_response){
            $this->assertArrayHasKey('id', $tag_in_response);
            $this->assertArrayHasKey('tag', $tag_in_response);
            $this->assertTrue(in_array($tag_in_response, $generated_tags_as_array));
        }
        $this->assertArrayHasKey('attachments', $response_body_as_array);
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
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));

        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($generated_entry->id, $response_body_as_array['id']);
        $this->assertArrayHasKey('entry_date', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_date, $response_body_as_array['entry_date']);
        $this->assertArrayHasKey('entry_value', $response_body_as_array);
        $this->assertEquals($generated_entry->entry_value, $response_body_as_array['entry_value']);
        $this->assertArrayHasKey('memo', $response_body_as_array);
        $this->assertEquals($generated_entry->memo, $response_body_as_array['memo']);
        $this->assertArrayHasKey('expense', $response_body_as_array);
        $this->assertEquals($generated_entry->expense, $response_body_as_array['expense']);
        $this->assertArrayHasKey('confirm', $response_body_as_array);
        $this->assertEquals($generated_entry->confirm, $response_body_as_array['confirm']);
        $this->assertArrayHasKey('create_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->create_stamp, $response_body_as_array['create_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('modified_stamp', $response_body_as_array);
        $this->assertEquals($generated_entry->modified_stamp, $response_body_as_array['modified_stamp'], "for these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertArrayHasKey('account_type', $response_body_as_array);
        $this->assertEquals($generated_entry->account_type, $response_body_as_array['account_type']);
        $this->assertArrayHasKey('tags', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['tags']));
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertArrayHasKey('attachments', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['attachments']));
        $this->assertEmpty($response_body_as_array['attachments']);
    }

    public function testGetEntryThatIsMarkedDeleted(){
//        $this->markTestIncomplete("Scenario: attempt to retrieve an entry when entry is marked as 'deleted'");
        // GIVEN
        $generated_entry = factory(Entry::class)->create(['deleted'=>1]);

        // WHEN
        $response = $this->get('/api/entry/'.$generated_entry->id);

        // THEN
        $response->assertStatus(404);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }


}