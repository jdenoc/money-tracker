<?php

namespace Tests\Feature\Api\Post;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Attachment;
use App\Models\Entry;
use App\Models\Tag;
use App\Traits\EntryResponseKeys;
use Brick\Money\Money;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PostEntryTest extends TestCase {
    use EntryResponseKeys;

    // uri
    private string $_base_uri = '/api/entry';

    public function testCreateEntryWithoutData() {
        // GIVEN
        $entry_data = [];

        // WHEN
        $response = $this->postJson($this->_base_uri, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, self::$ERROR_MSG_SAVE_ENTRY_NO_DATA);
    }

    public static function providerCreateEntryWithMissingData(): array {
        $required_entry_fields = Entry::get_fields_required_for_creation();

        $test_cases = [];
        // provide data that is missing one property
        foreach ($required_entry_fields as $required_entry_field) {
            $test_cases[$required_entry_field] = ['missing_properties' => [$required_entry_field]];
        }

        // provide data that is missing two or more properties, but 1 less than the total properties
        $random_missing_properties = array_rand(array_flip($required_entry_fields), mt_rand(2, count($required_entry_fields) - 1));
        $test_cases['missing:'.json_encode($random_missing_properties)] = ['missing_properties' => $random_missing_properties];

        return $test_cases;
    }

    /**
     * @dataProvider providerCreateEntryWithMissingData
     */
    public function testCreateEntryWithMissingData(array $missing_properties) {
        // GIVEN
        $entry_data = $this->generateEntryData();
        $entry_data = array_diff_key($entry_data, array_flip($missing_properties));

        // WHEN
        $response = $this->postJson($this->_base_uri, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, sprintf(self::$ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode($missing_properties)));
    }

    public function testCreateEntryButAccountTypeDoesNotExist() {
        // GIVEN
        $entry_data = $this->generateEntryData();

        // WHEN
        $response = $this->postJson($this->_base_uri, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, self::$ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE);
    }

    public function testCreateEntryAndAccountTotalUpdate() {
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->for($generated_account)->create();
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['account_type_id'] = $generated_account_type->id;

        // WHEN
        $get_account_response1 = $this->get('/api/account/'.$generated_account->id);
        // THEN
        $this->assertResponseStatus($get_account_response1, HttpStatus::HTTP_OK);
        $get_account_response1_as_array = $get_account_response1->json();
        $this->assertArrayHasKey('total', $get_account_response1_as_array);
        $this->assertArrayHasKey('currency', $get_account_response1_as_array);
        $original_account_total = Money::of($get_account_response1_as_array['total'], $get_account_response1_as_array['currency']);
        $this->assertArrayHasKey('account_types', $get_account_response1_as_array);
        $this->assertIsArray($get_account_response1_as_array['account_types']);
        $this->assertNotEmpty($get_account_response1_as_array['account_types']);

        // WHEN
        $post_response = $this->postJson($this->_base_uri, $generated_entry_data);
        // THEN
        $this->assertResponseStatus($post_response, HttpStatus::HTTP_CREATED);
        $post_response_as_array = $post_response->json();
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEmpty($post_response_as_array[self::$RESPONSE_SAVE_KEY_ERROR]);
        $created_entry_id = $post_response_as_array[self::$RESPONSE_SAVE_KEY_ID];
        $this->assertGreaterThan(self::$ERROR_ENTRY_ID, $created_entry_id);

        // WHEN
        $get_entry_response = $this->get($this->_base_uri.'/'.$created_entry_id);
        // THEN
        $this->assertResponseStatus($get_entry_response, HttpStatus::HTTP_OK);
        $get_entry_response_as_array = $get_entry_response->json();
        $this->assertEquals($generated_entry_data['entry_date'], $get_entry_response_as_array['entry_date']);
        $this->assertEquals($generated_entry_data['entry_value'], $get_entry_response_as_array['entry_value']);
        $this->assertEquals($generated_entry_data['memo'], $get_entry_response_as_array['memo']);
        $this->assertEquals($generated_entry_data['expense'], $get_entry_response_as_array['expense']);
        $this->assertEquals($generated_entry_data['confirm'], $get_entry_response_as_array['confirm']);
        $this->assertEquals($generated_entry_data['account_type_id'], $get_entry_response_as_array['account_type_id']);
        $this->assertNull($get_entry_response_as_array['transfer_entry_id']);

        // WHEN
        $get_account_response2 = $this->get('/api/account/'.$generated_account->id);
        // THEN
        $this->assertResponseStatus($get_account_response2, HttpStatus::HTTP_OK);
        $get_account_response2_as_array = $get_account_response2->json();
        $this->assertArrayHasKey('total', $get_account_response2_as_array);
        $this->assertArrayHasKey('currency', $get_account_response2_as_array);
        $new_account_total = Money::of($get_account_response2_as_array['total'], $get_account_response2_as_array['currency']);

        $entry_value = Money::of($generated_entry_data['expense'], $generated_account->currency)
            ->multipliedBy(($generated_entry_data['expense'] ? -1 : 1));
        $new_account_total->isEqualTo($original_account_total->plus($entry_value));
    }

    public function testCreateEntryWithTagsButOneTagDoesNotExist() {
        // GIVEN
        $generate_tag_count = 3;
        $generated_tags = Tag::factory()->count($generate_tag_count)->create();
        $generated_tag_ids = $generated_tags->pluck('id')->toArray();
        do {
            $non_existent_tag_id = fake()->randomNumber();
        } while (in_array($non_existent_tag_id, $generated_tag_ids));

        $generated_account_type = AccountType::factory()->for(Account::factory())->create();
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['tags'] = [$non_existent_tag_id];
        $generated_entry_data['tags'] = array_merge($generated_entry_data['tags'], $generated_tag_ids);
        $generated_entry_data['account_type_id'] = $generated_account_type->id;

        // WHEN
        $post_response = $this->postJson($this->_base_uri, $generated_entry_data);
        // THEN
        $this->assertResponseStatus($post_response, HttpStatus::HTTP_CREATED);
        $post_response_as_array = $post_response->json();
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEmpty($post_response_as_array[self::$RESPONSE_SAVE_KEY_ERROR]);
        $created_entry_id = $post_response_as_array[self::$RESPONSE_SAVE_KEY_ID];
        $this->assertGreaterThan(self::$ERROR_ENTRY_ID, $created_entry_id);

        // WHEN
        $get_response = $this->get($this->_base_uri.'/'.$created_entry_id);
        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $failure_message = "Response does not match generated data.\n\$generated_entry_data:".print_r($generated_entry_data, true)."\nResponse:".$get_response->getContent();
        $this->assertEquals($generated_entry_data['entry_date'], $get_response_as_array['entry_date'], $failure_message);
        $this->assertEquals($generated_entry_data['entry_value'], $get_response_as_array['entry_value'], $failure_message);
        $this->assertEquals($generated_entry_data['memo'], $get_response_as_array['memo'], $failure_message);
        $this->assertEquals($generated_entry_data['expense'], $get_response_as_array['expense'], $failure_message);
        $this->assertEquals($generated_entry_data['confirm'], $get_response_as_array['confirm'], $failure_message);
        $this->assertEquals($generated_entry_data['account_type_id'], $get_response_as_array['account_type_id'], $failure_message);
        $this->assertNull($get_response_as_array['transfer_entry_id'], $failure_message);
        $this->assertTrue(is_array($get_response_as_array['tags']), $failure_message);
        $this->assertNotEmpty($get_response_as_array['tags'], $failure_message);
        $this->assertFalse(in_array($non_existent_tag_id, $get_response_as_array['tags']), $failure_message);
        foreach ($get_response_as_array['tags'] as $entry_tag) {
            $this->assertContains(
                $entry_tag['id'],
                $generated_tag_ids,
                "Generated tag IDs: ".json_encode($generated_tag_ids)."\nEntry tags in response:".json_encode($get_response_as_array['tags'])
            );
        }
    }

    public function testCreateEntryWithAttachments() {
        // GIVEN
        $generated_attachments = Attachment::factory()->count(fake()->randomDigitNotZero())->make();
        $generated_account_type = AccountType::factory()->for(Account::factory())->create();
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['account_type_id'] = $generated_account_type->id;
        $generated_entry_data['attachments'] = [];
        foreach ($generated_attachments as $generated_attachment) {
            $generated_entry_data['attachments'][] = [
                'uuid' => $generated_attachment->uuid,
                'name' => $generated_attachment->name,
            ];
        }

        // WHEN
        $post_response = $this->postJson($this->_base_uri, $generated_entry_data);

        // THEN
        $this->assertResponseStatus($post_response, HttpStatus::HTTP_CREATED);
        $post_response_as_array = $post_response->json();
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEmpty($post_response_as_array[self::$RESPONSE_SAVE_KEY_ERROR]);
        $created_entry_id = $post_response_as_array[self::$RESPONSE_SAVE_KEY_ID];
        $this->assertGreaterThan(self::$ERROR_ENTRY_ID, $created_entry_id);

        // WHEN
        $get_response = $this->get($this->_base_uri.'/'.$created_entry_id);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $this->assertEquals($generated_entry_data['entry_date'], $get_response_as_array['entry_date']);
        $this->assertEquals($generated_entry_data['entry_value'], $get_response_as_array['entry_value']);
        $this->assertEquals($generated_entry_data['memo'], $get_response_as_array['memo']);
        $this->assertEquals($generated_entry_data['expense'], $get_response_as_array['expense']);
        $this->assertEquals($generated_entry_data['confirm'], $get_response_as_array['confirm']);
        $this->assertEquals($generated_entry_data['account_type_id'], $get_response_as_array['account_type_id']);
        $this->assertNull($get_response_as_array['transfer_entry_id']);
        $this->assertArrayHasKey('attachments', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['attachments']));
        $this->assertNotEmpty($get_response_as_array['attachments']);
        foreach ($get_response_as_array['attachments'] as $attachment) {
            $attachment_data = [
                'uuid' => $attachment['uuid'],
                'name' => $attachment['name'],
            ];
            $this->assertContains($attachment_data, $generated_entry_data['attachments'], 'Generated attachments:'.json_encode($generated_entry_data['attachments']));
        }
    }

    public function testCreateEntryWithRelatedTransferEntryId() {
        // GIVEN
        $generated_account_type = AccountType::factory()->for(Account::factory())->create();
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['account_type_id'] = $generated_account_type->id;
        $generated_transfer_entry = Entry::factory()->create($generated_entry_data);
        $generated_entry_data['transfer_entry_id'] = $generated_transfer_entry->id;

        // WHEN
        $post_response = $this->postJson($this->_base_uri, $generated_entry_data);

        // THEN
        $this->assertResponseStatus($post_response, HttpStatus::HTTP_CREATED);
        $post_response_as_array = $post_response->json();
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEmpty($post_response_as_array[self::$RESPONSE_SAVE_KEY_ERROR]);
        $created_entry_id = $post_response_as_array[self::$RESPONSE_SAVE_KEY_ID];
        $this->assertGreaterThan(self::$ERROR_ENTRY_ID, $created_entry_id);

        // WHEN
        $get_response = $this->get($this->_base_uri.'/'.$created_entry_id);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $get_response->json();
        $failure_message = "Response does not match generated data.\n\$generated_entry_data:".print_r($generated_entry_data, true)."\nResponse:".$get_response->getContent();
        $this->assertEquals($generated_entry_data['entry_date'], $get_response_as_array['entry_date'], $failure_message);
        $this->assertEquals($generated_entry_data['entry_value'], $get_response_as_array['entry_value'], $failure_message);
        $this->assertEquals($generated_entry_data['memo'], $get_response_as_array['memo'], $failure_message);
        $this->assertEquals($generated_entry_data['expense'], $get_response_as_array['expense'], $failure_message);
        $this->assertEquals($generated_entry_data['confirm'], $get_response_as_array['confirm'], $failure_message);
        $this->assertEquals($generated_entry_data['account_type_id'], $get_response_as_array['account_type_id'], $failure_message);
        $this->assertNotNull($get_response_as_array['transfer_entry_id'], $failure_message);
        $this->assertEquals($generated_entry_data['transfer_entry_id'], $get_response_as_array['transfer_entry_id'], $failure_message);
    }

    private function generateEntryData(): array {
        $entry_data = Entry::factory()->make();
        return [
            'account_type_id' => $entry_data->account_type_id,
            'confirm' => $entry_data->confirm,
            'entry_date' => $entry_data->entry_date,
            'entry_value' => $entry_data->entry_value,
            'expense' => $entry_data->expense,
            'memo' => $entry_data->memo,
        ];
    }

    private function assertPostResponseHasCorrectKeys(array $response_as_array): void {
        $failure_message = "POST Response is ".json_encode($response_as_array);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ERROR, $response_as_array, $failure_message);
    }

    private function assertFailedPostResponse(array $response_as_array, string $response_error_msg): void {
        $failure_message = "POST Response is ".json_encode($response_as_array);
        $this->assertEquals(self::$ERROR_ENTRY_ID, $response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertStringContainsString($response_error_msg, $response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
    }

}
