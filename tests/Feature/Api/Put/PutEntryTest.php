<?php

namespace Tests\Feature\Api\Put;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Attachment;
use App\Models\Entry;
use App\Models\Tag;
use App\Traits\EntryResponseKeys;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PutEntryTest extends TestCase {
    use EntryResponseKeys;
    use WithFaker;

    // uri
    private string $_base_uri = '/api/entry/';

    // objects
    private $_generated_account;
    private $_generated_account_type;
    private $_generated_entry;

    public function setUp(): void {
        parent::setUp();
        // GIVEN - for all tests
        $this->_generated_account = Account::factory()->create();
        $this->_generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $this->_generated_entry = Entry::factory()->for($this->_generated_account_type)->create();
    }

    public function testUpdateEntryWithoutProvidingData() {
        // GIVEN - see setUp()
        $update_data = [];

        // WHEN
        $response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $update_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPutResponse($response_as_array, self::$ERROR_MSG_SAVE_ENTRY_NO_DATA);
    }

    public function testUpdateEntryButNewAccountTypeDoesNotExist() {
        // GIVEN - see setUp()
        $entry_data = Entry::factory()->for($this->_generated_account_type)->make();
        $entry_data = $entry_data->toArray();
        // make sure account_type_id value that does not exist
        do {
            $entry_data['account_type_id'] = fake()->randomDigitNotZero();
        } while($entry_data['account_type_id'] == $this->_generated_account_type->id);

        // WHEN
        $response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $entry_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPutResponse($response_as_array, self::$ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE);
    }

    public function testUpdateEntryButEntryDoesNotExist() {
        // GIVEN - entry does not exist
        do {
            // make sure randomly generated entry ID isn't associated
            // with the pre-generated entry
            $entry_id = fake()->randomNumber();
        } while ($entry_id == $this->_generated_entry->id);
        $entry_data = Entry::factory()->for($this->_generated_account_type)->make();
        $entry_data = $entry_data->toArray();

        // WHEN
        $response = $this->putJson($this->_base_uri.$entry_id, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPutResponse($response_as_array, self::$ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST);
    }

    public function testUpdateEntryAndConfirmAccountTotalUpdated() {
        // GIVEN - see setUp()
        $entry_data = Entry::factory()->for($this->_generated_account_type)->make();
        $entry_data = [
            'entry_value' => $entry_data->entry_value,
        ];

        // WHEN
        $get_account_response1 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account_response1->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response1_as_array = $get_account_response1->json();
        $this->assertArrayHasKey('total', $get_account_response1_as_array);
        $original_total = Money::of($get_account_response1_as_array['total'], $get_account_response1_as_array['currency']);

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $entry_data);
        // THEN
        $this->assertResponseStatus($put_response, HttpStatus::HTTP_OK);
        $put_response_as_array = $put_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_entry_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_entry_response->assertStatus(HttpStatus::HTTP_OK);
        $get_entry_response_as_array = $get_entry_response->json();
        $this->assertArrayHasKey('entry_value', $get_entry_response_as_array);
        $this->assertEquals($entry_data['entry_value'], $get_entry_response_as_array['entry_value']);

        // WHEN
        $get_account_response2 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account_response2->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response2_as_array = $get_account_response2->json();
        $this->assertArrayHasKey('total', $get_account_response2_as_array);
        $updated_total = Money::of($get_account_response2_as_array['total'], $get_account_response2_as_array['currency']);

        $original_entry_value = Money::of($this->_generated_entry->entry_value, $this->_generated_account->currency)
            ->multipliedBy(($this->_generated_entry->expense ? -1 : 1));
        $new_entry_value = Money::of($get_entry_response_as_array['entry_value'], $this->_generated_account->currency)
            ->multipliedBy(($get_entry_response_as_array['expense'] ? -1 : 1));

        $this->assertTrue(
            $original_total
                ->minus($original_entry_value)
                ->plus($new_entry_value)
                ->isEqualTo($updated_total),
            "total\t\t| original:$original_total | update:$updated_total\nentry value\t| original:$original_entry_value | updated:$new_entry_value"
        );
    }

    public function testUpdateEntryAndChangeAccountTypeCausingAccountTotalsToUpdate() {
        // GIVEN - see setUp()
        $generated_account2 = Account::factory()->state(['currency' => $this->_generated_account->currency])->create();
        $generated_account_type2 = AccountType::factory()->for($generated_account2)->create();
        $entry_data = $this->_generated_entry->toArray();
        $entry_data['account_type_id'] = $generated_account_type2->id;

        // WHEN - checking account 1 original total
        $get_account1_response1 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account1_response1->assertStatus(HttpStatus::HTTP_OK);
        $get_account1_response1_as_array = $get_account1_response1->json();
        $this->assertArrayHasKey('total', $get_account1_response1_as_array);
        $account1_original_total = Money::of($get_account1_response1_as_array['total'], $get_account1_response1_as_array['currency']);

        // WHEN - checking account 2 original total
        $get_account2_response1 = $this->get('/api/account/'.$generated_account2->id);
        // THEN
        $get_account2_response1->assertStatus(HttpStatus::HTTP_OK);
        $get_account2_response1_as_array = $get_account2_response1->json();
        $this->assertArrayHasKey('total', $get_account2_response1_as_array);
        $account2_original_total = Money::of($get_account2_response1_as_array['total'], $get_account2_response1_as_array['currency']);

        // WHEN - updated entry.account_type_id
        $update_entry_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $entry_data);
        // THEN
        $this->assertResponseStatus($update_entry_response, HttpStatus::HTTP_OK);
        $put_response_as_array = $update_entry_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN - confirming entry.account_type_id was updated
        $get_entry_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $this->assertResponseStatus($get_entry_response, HttpStatus::HTTP_OK);
        $get_entry_response_as_array = $get_entry_response->json();
        $this->assertArrayHasKey('account_type_id', $get_entry_response_as_array);
        $this->assertEquals($entry_data['account_type_id'], $get_entry_response_as_array['account_type_id']);

        // WHEN - checking account 1 new total
        $get_account1_response2 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account1_response2->assertStatus(HttpStatus::HTTP_OK);
        $get_account1_response2_as_array = $get_account1_response2->json();
        $this->assertArrayHasKey('total', $get_account1_response2_as_array);
        $account1_new_total = Money::of($get_account1_response2_as_array['total'], $get_account1_response2_as_array['currency']);

        // WHEN - checking account 2 new total
        $get_account2_response2 = $this->get('/api/account/'.$generated_account2->id);
        // THEN
        $get_account2_response2->assertStatus(HttpStatus::HTTP_OK);
        $get_account2_response2_as_array = $get_account2_response2->json();
        $this->assertArrayHasKey('total', $get_account2_response2_as_array);
        $account2_new_total = Money::of($get_account2_response2_as_array['total'], $get_account2_response2_as_array['currency']);

        // confirm account totals have been updated
        $entry_value = Money::of($this->_generated_entry->entry_value, $this->_generated_account->currency)
            ->multipliedBy(($this->_generated_entry->expense ? -1 : 1));
        $error_message  = "\n - Entry value:".$entry_value;
        $error_message .= "\n - [account1] Original total:".$account1_original_total."\n - [account1] New total:".$account1_new_total;
        $error_message .= "\n - [account2] Original total:".$account2_original_total."\n - [account2] New total:".$account2_new_total;
        $this->assertTrue(
            $account1_original_total->minus($entry_value)->isEqualTo($account1_new_total),
            "Account1 total comparison failing".$error_message
        );
        $this->assertTrue(
            $account2_original_total->plus($entry_value)->isEqualTo($account2_new_total),
            "Account total comparison failing".$error_message
        );
    }

    public static function providerUpdateEntryWithCertainProperties(): array {
        $required_entry_fields = Entry::get_fields_required_for_update();
        unset($required_entry_fields[array_search('entry_value', $required_entry_fields)]);
        unset($required_entry_fields[array_search('account_type_id', $required_entry_fields)]);
        $required_entry_fields = array_values($required_entry_fields);  // do this to reset array index after unset

        $update_entry_fields = [];
        foreach ($required_entry_fields as $required_entry_field) {
            $update_entry_fields["update [$required_entry_field]"] = ['entry_data_fields' => [$required_entry_field]];
        }

        $update_batch_properties = [];
        $batch_entry_fields = array_rand($required_entry_fields, mt_rand(2, count($required_entry_fields) - 1));
        $batch_entry_fields = array_intersect_key($required_entry_fields, array_flip($batch_entry_fields));
        foreach ($batch_entry_fields as $entry_property) {
            $update_batch_properties[$entry_property] = $entry_property;
        }
        $update_entry_fields['update ['.implode(',', $batch_entry_fields).']'] = ['entry_data_fields' => $update_batch_properties];

        return $update_entry_fields;
    }

    /**
     * @dataProvider providerUpdateEntryWithCertainProperties
     */
    public function testUpdateEntryWithCertainProperties(array $entry_data_fields) {
        // GIVEN - see setUp()
        $generated_entry_data = Entry::factory()->make()->toArray();
        $entry_data = array_intersect_key($generated_entry_data, array_flip($entry_data_fields));

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $put_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response->assertStatus(HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        foreach ($entry_data as $property => $value) {
            $this->assertEquals($value, $get_response_as_array[$property], "Entry data:".json_encode($entry_data)."\nGET Response:".$get_response->getContent());
        }
    }

    public function testUpdateEntryToHaveTransferEntryCounterpart() {
        // GIVEN - see setup()
        $entry_data = ['transfer_entry_id' => fake()->randomDigitNotZero()];

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response_as_array = $get_response->json();
        $original_transfer_entry_id = $get_response_as_array['transfer_entry_id'];
        $this->assertNull($original_transfer_entry_id);

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $entry_data);
        // THEN
        $this->assertPutResponseHasCorrectKeys($put_response->json());
        $this->assertSuccessPutResponse($put_response->json());

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response_as_array = $get_response->json();
        $this->assertNotNull($get_response_as_array['transfer_entry_id']);
        $this->assertNotEquals($original_transfer_entry_id, $get_response_as_array['transfer_entry_id']);
        $this->assertEquals($entry_data['transfer_entry_id'], $get_response_as_array['transfer_entry_id']);
    }

    public function testUpdateEntryWithTagThatDoesNotExist() {
        // GIVEN - see setUp()
        $generate_tag_count = fake()->numberBetween(2, 5);
        Tag::factory($generate_tag_count)->create();
        $put_entry_data = ['tags' => Tag::all()->pluck('id')->toArray()];
        do {
            $non_existent_tag_id = fake()->unique()->randomNumber(3);
        } while (in_array($non_existent_tag_id, $put_entry_data['tags']));
        $put_entry_data['tags'][] = $non_existent_tag_id;

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $put_entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $put_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response->assertStatus(HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $this->assertArrayHasKey('tags', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['tags']));
        $this->assertNotEmpty($get_response_as_array['tags']);
        foreach ($get_response_as_array['tags'] as $idx => $response_tag) {
            $this->assertContains($response_tag['id'], $put_entry_data['tags']);
            unset($get_response_as_array['tags'][$idx]);
        }
        $this->assertEmpty($get_response_as_array['tags']);
    }

    public function testUpdateEntryWithTagsSoTheyAreNotDuplicated() {
        // GIVEN - see setUp()
        $generate_tag_count = fake()->numberBetween(2, 5);
        $generated_tags = Tag::factory()->count($generate_tag_count)->create();
        $generated_tag_ids = $generated_tags->pluck('pivot.tag_id')->toArray();
        $attaching_tag_id = fake()->randomElement($generated_tag_ids);
        $this->_generated_entry->tags()->attach($attaching_tag_id);
        $put_entry_data = ['tags' => [$attaching_tag_id]];
        $put_entry_data['tags'] = array_merge(
            $put_entry_data['tags'],
            [$attaching_tag_id],
            fake()->randomElements($generated_tag_ids, 2)
        );

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $put_entry_data);
        // THEN
        $this->assertResponseStatus($put_response, HttpStatus::HTTP_OK);
        $put_response_as_array = $put_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $unique_tags_in_response = array_unique($get_response_as_array['tags'], SORT_REGULAR);
        $this->assertCount(count($get_response_as_array['tags']), $unique_tags_in_response, $get_response->getContent());
    }

    public function testUpdateEntryWithAttachments() {
        // GIVEN
        $attachment_data = Attachment::factory()->make();
        $put_entry_data = ['attachments' => [[
            'uuid' => $attachment_data->uuid,
            'name' => $attachment_data->name,
        ]]];

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $put_entry_data);
        // THEN
        $this->assertResponseStatus($put_response, HttpStatus::HTTP_OK);
        $put_response_as_array = $put_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $this->assertArrayHasKey('attachments', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['attachments']));
        $this->assertNotEmpty($get_response_as_array['attachments']);
        foreach ($get_response_as_array['attachments'] as $response_attachment) {
            $attachment_data = [
                'uuid' => $response_attachment['uuid'],
                'name' => $response_attachment['name'],
            ];
            $this->assertContains($attachment_data, $put_entry_data['attachments'], 'Generated attachments:'.json_encode($put_entry_data['attachments']));
        }
    }

    public function testUpdateEntryWithAttachmentsAndAttachmentsAreAlreadyAttached() {
        // GIVEN
        $unattached_attachment = Attachment::factory()->make();
        $put_entry_data = ['attachments' => [
            ['uuid' => $unattached_attachment->uuid, 'name' => $unattached_attachment->name],
        ]];
        $attached_attachments = Attachment::factory()->count(3)->create(['entry_id' => $this->_generated_entry->id]);
        foreach ($attached_attachments as $attached_attachment) {
            $put_entry_data['attachments'][] = ['uuid' => $attached_attachment->uuid, 'name' => $attached_attachment->name];
        }

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $put_entry_data);

        // THEN
        $this->assertResponseStatus($put_response, HttpStatus::HTTP_OK);
        $put_response_as_array = $put_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $this->assertArrayHasKey('attachments', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['attachments']));
        $this->assertNotEmpty($get_response_as_array['attachments']);
        foreach ($get_response_as_array['attachments'] as $response_attachment) {
            $attachment_data = [
                'uuid' => $response_attachment['uuid'],
                'name' => $response_attachment['name'],
            ];
            // This step really makes sure that entries have not been duplicated
            $this->assertContains($attachment_data, $put_entry_data['attachments'], 'Generated attachments:'.json_encode($put_entry_data['attachments']));
        }
    }

    public function testUpdateEntryWithNoUpdates() {
        // GIVEN
        $put_entry_data = $this->_generated_entry->toArray();

        // WHEN
        $put_response = $this->putJson($this->_base_uri.$this->_generated_entry->id, $put_entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $put_response->json();
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response->assertStatus(HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $this->assertEquals($put_entry_data['entry_date'], $get_response_as_array['entry_date'], $get_response->getContent());
        $this->assertEquals($put_entry_data['entry_value'], $get_response_as_array['entry_value'], $get_response->getContent());
        $this->assertEquals($put_entry_data['memo'], $get_response_as_array['memo'], $get_response->getContent());
        $this->assertEquals($put_entry_data['expense'], $get_response_as_array['expense'], $get_response->getContent());
        $this->assertEquals($put_entry_data['confirm'], $get_response_as_array['confirm'], $get_response->getContent());
        $this->assertEquals($put_entry_data['account_type_id'], $get_response_as_array['account_type_id'], $get_response->getContent());
    }

    private function assertPutResponseHasCorrectKeys(array $response_as_array): void {
        $failure_message = "PUT Response is ".json_encode($response_as_array);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ERROR, $response_as_array, $failure_message);
    }

    private function assertFailedPutResponse(array $response_as_array, string $response_error_msg): void {
        $failure_message = "PUT response is ".json_encode($response_as_array);
        $this->assertEquals(self::$ERROR_ENTRY_ID, $response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertStringContainsString($response_error_msg, $response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
    }

    private function assertSuccessPutResponse(array $response_as_array): void {
        $failure_message = "PUT response is ".json_encode($response_as_array);
        $this->assertEmpty($response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertGreaterThan(self::$ERROR_ENTRY_ID, $response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertEquals($this->_generated_entry->id, $response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message." while updating entry ID ".$this->_generated_entry->id);
    }

}
