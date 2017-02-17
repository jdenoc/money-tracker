<?php

namespace Tests\Feature\Api;

use App\Attachment;
use App\Entry;
use Tests\TestCase;
use Faker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

class DeleteAttachmentTest extends TestCase {

    use DatabaseMigrations;

    private $_attachment_base_uri = '/api/attachment/';
    private $_entry_base_uri = '/api/entry/';

    public function testDeleteAttachmentWhenNoRecordsExist(){
        $faker = Faker\Factory::create();
        // GIVEN - no attachment records
        $uuid = $faker->uuid;

        // WHEN
        $response = $this->delete($this->_attachment_base_uri.$uuid);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteAttachment(){
        // GIVEN
        $entry = factory(Entry::class)->create();
        $attachment = factory(Attachment::class)->create(['entry_id'=>$entry->id]);

        // WHEN
        $get_response1 = $this->get($this->_entry_base_uri.$entry->id);
        $delete_response = $this->delete($this->_attachment_base_uri.$attachment->uuid);
        $get_response2 = $this->get($this->_entry_base_uri.$entry->id);

        // THEN
        $get_response1->assertStatus(Response::HTTP_OK);
        $get_response1_as_array = $this->getResponseAsArray($get_response1);
        $this->assertTrue(is_array($get_response1_as_array));
        $this->assertArrayHasKey('attachments', $get_response1_as_array);
        $this->assertTrue(is_array($get_response1_as_array['attachments']));
        $attachment_exists = false;
        foreach($get_response1_as_array['attachments'] as $attachment_in_response){
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            if($attachment_in_response['uuid'] == $attachment->uuid){
                $attachment_exists = true;
                break;
            }
        }
        $this->assertTrue($attachment_exists);
        unset($attachment_exists);

        $delete_response->assertStatus(Response::HTTP_NO_CONTENT);

        $get_response2->assertStatus(Response::HTTP_OK);
        $get_response2_as_array = $this->getResponseAsArray($get_response2);
        $this->assertTrue(is_array($get_response2_as_array));
        $this->assertArrayHasKey('attachments', $get_response2_as_array);
        $this->assertTrue(is_array($get_response2_as_array['attachments']));
        $attachment_exists = false;
        foreach($get_response2_as_array['attachments'] as $attachment_in_response){
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            if($attachment_in_response['uuid'] == $attachment->uuid){
                $attachment_exists = true;
                break;
            }
        }
        $this->assertFalse($attachment_exists);
    }

}