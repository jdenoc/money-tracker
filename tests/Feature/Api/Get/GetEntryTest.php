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

    public function setUp(): void {
        parent::setUp();

        $this->_generate_attachment_count = $this->faker->randomDigitNotZero();
        $this->_generate_tag_count = $this->faker->randomDigitNotZero();
    }

    public function testGetEntryWithNoData() {
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

    public function testGetEntryData() {
        // GIVEN
        /** @var Entry $generated_entry */
        $generated_entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->create();
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertEntryNodeExcludingRelationships($response_body_as_array, $generated_entry);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryDataWithRelatedTransferEntry() {
        // GIVEN
        $generated_account_type = AccountType::factory()->for(Account::factory())->create();
        /** @var Entry $generated_transfer_entry */
        $generated_transfer_entry = Entry::factory()->for($generated_account_type)->create();
        /** @var Entry $generated_entry */
        $generated_entry = Entry::factory()->for($generated_account_type)->state(['transfer_entry_id'=>$generated_transfer_entry->id])->create();
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertEntryNodeExcludingRelationships($response_body_as_array, $generated_entry);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryDataWithNoAssociatedTags() {
        // GIVEN
        /** @var Entry $generated_entry */
        $generated_entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->create();
        $generated_attachments_as_array = $this->generateAttachmentsAndOutputAsArray($generated_entry->id);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertEntryNodeExcludingRelationships($response_body_as_array, $generated_entry);
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertEntryAttachmentsNodeOK($response_body_as_array['attachments'], $generated_attachments_as_array);
    }

    public function testGetEntryWithNoAssociatedAttachments() {
        // GIVEN
        /** @var Entry $generated_entry */
        $generated_entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->create();
        $generated_tags_as_array = $this->generateTagsAndOutputAsArray($generated_entry);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertEntryNodeExcludingRelationships($response_body_as_array, $generated_entry);
        $this->assertEntryTagsNodeOK($response_body_as_array['tags'], $generated_tags_as_array);
        $this->assertEmpty($response_body_as_array['attachments']);
    }

    public function testGetEntryWithNoAssociatedTagsAndAttachments() {
        // GIVEN
        /** @var Entry $generated_entry */
        $generated_entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->create();

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertEntryNodeExcludingRelationships($response_body_as_array, $generated_entry);
        $this->assertEmpty($response_body_as_array['tags']);
        $this->assertEmpty($response_body_as_array['attachments']);
    }

    public function testGetEntryThatIsMarkedDisabled() {
        // GIVEN
        /** @var Entry $generated_entry */
        $generated_entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->create(['disabled'=>true]);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_entry->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertEmpty($response_body_as_array);
    }

    private function generateAttachmentsAndOutputAsArray(int $generated_entry_id): array {
        $generated_attachments = Attachment::factory()->count($this->_generate_attachment_count)->create(['entry_id'=>$generated_entry_id]);
        $generated_attachments->makeHidden('entry_id');
        return $generated_attachments->toArray();
    }

    private function generateTagsAndOutputAsArray(Entry $generated_entry): array {
        $generated_tags = Tag::factory()->count($this->_generate_tag_count)->create();
        $generated_entry->tags()->attach($generated_tags->pluck('id')->toArray());
        return $generated_tags->toArray();
    }

    private function assertEntryNodeExcludingRelationships($entry_from_response, Entry $generated_entry) {
        $expected_elements = ['id', 'entry_date', 'entry_value', 'memo', 'expense', 'confirm', 'account_type_id', 'transfer_entry_id', Entry::CREATED_AT, Entry::UPDATED_AT, 'tags', 'attachments'];
        $this->assertEqualsCanonicalizing($expected_elements, array_keys($entry_from_response));

        $failure_message = 'generated entry:'.json_encode($generated_entry)."\nresponse entry:".json_encode($entry_from_response);
        foreach ($expected_elements as $element) {
            switch ($element) {
                case Entry::CREATED_AT:
                case Entry::UPDATED_AT:
                    $this->assertDateFormat($entry_from_response[$element], Carbon::ATOM, $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
                    $this->assertDatetimeWithinOneSecond($generated_entry->$element, $entry_from_response[$element], $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
                    break;
                case 'tags':
                case 'attachments':
                    $this->assertIsArray($entry_from_response[$element]);
                    break;
                default:
                    $this->assertEquals($generated_entry->$element, $entry_from_response[$element], $failure_message);
                    break;
            }
        }
    }

    private function assertEntryTagsNodeOK($entry_tags_node, array $generated_tags_as_array) {
        $this->assertCount($this->_generate_tag_count, $entry_tags_node);
        $expected_elements = ['id', 'name'];
        foreach ($entry_tags_node as $tag_in_response) {
            $this->assertEqualsCanonicalizing($expected_elements, array_keys($tag_in_response));
            $this->assertTrue(
                in_array($tag_in_response, $generated_tags_as_array),
                "tag in response:".json_encode($tag_in_response)."\ngenerated tags:".json_encode($generated_tags_as_array)
            );
        }
    }

    private function assertEntryAttachmentsNodeOK($entry_attachments_node, array $generated_attachments_as_array) {
        $this->assertCount($this->_generate_attachment_count, $entry_attachments_node);
        $expected_elements = ['uuid', 'name', 'stamp'];
        foreach ($entry_attachments_node as $attachment_in_response) {
            $this->assertEqualsCanonicalizing($expected_elements, array_keys($attachment_in_response));
            $this->assertTrue(
                in_array($attachment_in_response, $generated_attachments_as_array),
                "attachment in response:".json_encode($attachment_in_response)."\ngenerated attachments:".json_encode($generated_attachments_as_array)
            );
        }
    }

}
