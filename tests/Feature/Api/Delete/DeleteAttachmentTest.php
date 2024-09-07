<?php

namespace Tests\Feature\Api\Delete;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Attachment;
use App\Models\Entry;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteAttachmentTest extends TestCase {

    // uri
    private string $_attachment_base_uri = '/api/attachment/';
    private string $_entry_base_uri = '/api/entry/';

    public function testDeleteAttachmentWhenNoRecordsExist() {
        // GIVEN - no attachment records
        $uuid = fake()->uuid();

        // WHEN
        $response = $this->delete($this->_attachment_base_uri.$uuid);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteAttachment() {
        // GIVEN
        $generated_entry = Entry::factory()
            ->for(
                AccountType::factory()
                    ->for(Account::factory())
            )
            ->create();
        $generated_attachment = Attachment::factory()->for($generated_entry)->create();

        // WHEN
        $get_response1 = $this->get($this->_entry_base_uri.$generated_entry->id);
        $delete_response = $this->delete($this->_attachment_base_uri.$generated_attachment->uuid);
        $get_response2 = $this->get($this->_entry_base_uri.$generated_entry->id);

        // THEN
        $get_response1->assertStatus(Response::HTTP_OK);
        $get_response1_as_array = $get_response1->json();
        $this->assertIsArray($get_response1_as_array);
        $this->assertArrayHasKey('attachments', $get_response1_as_array);
        $this->assertIsArray($get_response1_as_array['attachments']);
        $attachment_exists = false;
        foreach ($get_response1_as_array['attachments'] as $attachment_in_response) {
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            if ($attachment_in_response['uuid'] == $generated_attachment->uuid) {
                $attachment_exists = true;
                break;
            }
        }
        $this->assertTrue($attachment_exists);
        unset($attachment_exists);

        $delete_response->assertStatus(Response::HTTP_NO_CONTENT);

        $get_response2->assertStatus(Response::HTTP_OK);
        $get_response2_as_array = $get_response2->json();
        $this->assertIsArray($get_response2_as_array);
        $this->assertArrayHasKey('attachments', $get_response2_as_array);
        $this->assertIsArray($get_response2_as_array['attachments']);
        $attachment_exists = false;
        foreach ($get_response2_as_array['attachments'] as $attachment_in_response) {
            $this->assertArrayHasKey('uuid', $attachment_in_response);
            if ($attachment_in_response['uuid'] == $generated_attachment->uuid) {
                $attachment_exists = true;
                break;
            }
        }
        $this->assertFalse($attachment_exists);
    }

}
