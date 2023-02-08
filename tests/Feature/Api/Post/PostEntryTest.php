<?php

namespace Tests\Feature\Api\Post;

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

class PostEntryTest extends TestCase {
    use EntryResponseKeys;
    use WithFaker;

    private string $_base_uri = '/api/entry';

    public function testCreateEntryWithoutData() {
        // GIVEN
        $entry_data = [];

        // WHEN
        $response = $this->json('POST', $this->_base_uri, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, self::$ERROR_MSG_SAVE_ENTRY_NO_DATA);
    }

    public function providerCreateEntryWithMissingData() {
        // PHPUnit data providers are called before setUp() and setUpBeforeClass() are called.
        // With that piece of information, we need to call setUp() earlier than we normally would so that we can use model factories
        //$this->setUp();
        // We can no longer call setUp() as a workaround
        // it caused the database to populate and in doing so we caused some tests to fail.
        // Said tests failed because they were testing the absence of database values.
        $this->initialiseApplication();

        $required_entry_fields = Entry::get_fields_required_for_creation();

        $missing_data_entries = [];
        // provide data that is missing one property
        for ($i=0; $i<count($required_entry_fields); $i++) {
            $entry_data = $this->generateEntryData();
            unset($entry_data[$required_entry_fields[$i]]);
            $missing_data_entries['missing ['.$required_entry_fields[$i].']'] = [
                $entry_data,
                sprintf(self::$ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode([$required_entry_fields[$i]]))
            ];
        }

        // provide data that is missing two or more properties, but 1 less than the total properties
        $entry_data = $this->generateEntryData();
        $unset_keys = array_rand($required_entry_fields, mt_rand(2, count($required_entry_fields)-1));
        $removed_keys = [];
        foreach ($unset_keys as $unset_key) {
            $removed_key = $required_entry_fields[$unset_key];
            unset($entry_data[$removed_key]);
            $removed_keys[] = $removed_key;
        }
        $missing_data_entries['missing ['.implode(',', $removed_keys).']'] = [
            $entry_data,
            sprintf(self::$ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode($removed_keys))
        ];

        return $missing_data_entries;
    }

    /**
     * @dataProvider providerCreateEntryWithMissingData
     * @param array $entry_data
     * @param string $expected_response_error_msg
     */
    public function testCreateEntryWithMissingData($entry_data, string $expected_response_error_msg) {
        // GIVEN - $entry_data by providerCreateEntryWithMissingData

        // WHEN
        $response = $this->json('POST', $this->_base_uri, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, $expected_response_error_msg);
    }

    public function testCreateEntryButAccountTypeDoesNotExist() {
        // GIVEN
        $entry_data = $this->generateEntryData();

        // WHEN
        $response = $this->json("POST", $this->_base_uri, $entry_data);

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
        $post_response = $this->json("POST", $this->_base_uri, $generated_entry_data);
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
        $failure_message = "Response does not match generated data.\n\$generated_entry_data:".print_r($generated_entry_data, true)."\nResponse:".$get_entry_response->getContent();
        $this->assertEntryBaseProperties($generated_entry_data, $get_entry_response_as_array, $failure_message);
        $this->assertNull($get_entry_response_as_array['transfer_entry_id'], $failure_message);

        // WHEN
        $get_account_response2 = $this->get('/api/account/'.$generated_account->id);
        // THEN
        $this->assertResponseStatus($get_account_response2, HttpStatus::HTTP_OK);
        $get_account_response2_as_array = $get_account_response2->json();
        $this->assertArrayHasKey('total', $get_account_response2_as_array);
        $this->assertArrayHasKey('currency', $get_account_response2_as_array);
        $new_account_total = Money::of($get_account_response2_as_array['total'], $get_account_response2_as_array['currency']);

        $entry_value = Money::of($generated_entry_data['entry_value'], $generated_account->currency)
            ->multipliedBy($generated_entry_data['expense'] ? -1 : 1);
        $this->assertTrue($original_account_total->plus($entry_value)->isEqualTo($new_account_total));
    }

    public function testCreateEntryWithTagsButOneTagDoesNotExist() {
        // GIVEN
        $generate_tag_count = 3;
        $generated_tags = Tag::factory()->count($generate_tag_count)->create();
        $generated_tag_ids = $generated_tags->pluck('id')->toArray();
        do {
            $non_existent_tag_id = $this->faker->randomNumber();
        } while (in_array($non_existent_tag_id, $generated_tag_ids));

        $generated_account_type = AccountType::factory()->for(Account::factory())->create();
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['tags'] = [$non_existent_tag_id];
        $generated_entry_data['tags'] = array_merge($generated_entry_data['tags'], $generated_tag_ids);
        $generated_entry_data['account_type_id'] = $generated_account_type->id;

        // WHEN
        $post_response = $this->json('POST', $this->_base_uri, $generated_entry_data);
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
        $this->assertEntryBaseProperties($generated_entry_data, $get_response_as_array, $failure_message);
        $this->assertNull($get_response_as_array['transfer_entry_id'], $failure_message);
        $this->assertIsArray($get_response_as_array['tags'], $failure_message);
        $this->assertNotEmpty($get_response_as_array['tags'], $failure_message);
        $this->assertNotContains($non_existent_tag_id, $get_response_as_array['tags'], $failure_message);
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
        $generated_attachments = Attachment::factory()->count($this->faker->randomDigitNotZero())->make();
        $generated_account_type = AccountType::factory()->for(Account::factory())->create();
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['account_type_id'] = $generated_account_type->id;
        $generated_entry_data['attachments'] = [];
        foreach ($generated_attachments as $generated_attachment) {
            $generated_entry_data['attachments'][] = [
                'uuid'=>$generated_attachment->uuid,
                'name'=>$generated_attachment->name
            ];
        }

        // WHEN
        $post_response = $this->json("POST", $this->_base_uri, $generated_entry_data);

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
        $this->assertEntryBaseProperties($generated_entry_data, $get_response_as_array, $failure_message);
        $this->assertNull($get_response_as_array['transfer_entry_id'], $failure_message);
        $this->assertArrayHasKey('attachments', $get_response_as_array, $failure_message);
        $this->assertIsArray($get_response_as_array['attachments'], $failure_message);
        $this->assertNotEmpty($get_response_as_array['attachments'], $failure_message);
        foreach ($get_response_as_array['attachments'] as $attachment) {
            $attachment_data = [
                'uuid'=>$attachment['uuid'],
                'name'=>$attachment['name']
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
        $post_response = $this->json("POST", $this->_base_uri, $generated_entry_data);

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
        $this->assertEntryBaseProperties($generated_entry_data, $get_response_as_array, $failure_message);
        $this->assertNotNull($get_response_as_array['transfer_entry_id'], $failure_message);
        $this->assertEquals($generated_entry_data['transfer_entry_id'], $get_response_as_array['transfer_entry_id'], $failure_message);
    }

    private function generateEntryData(): array {
        $entry_data = Entry::factory()->make();
        return [
            'account_type_id'=>$entry_data->account_type_id,
            'confirm'=>$entry_data->confirm,
            'entry_date'=>$entry_data->entry_date,
            'entry_value'=>$entry_data->entry_value,
            'expense'=>$entry_data->expense,
            'memo'=>$entry_data->memo
        ];
    }

    private function assertPostResponseHasCorrectKeys(array $response_as_array) {
        $failure_message = "POST Response is ".json_encode($response_as_array);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ERROR, $response_as_array, $failure_message);
    }

    /**
     * @param array $response_as_array
     * @param string $response_error_msg
     */
    private function assertFailedPostResponse(array $response_as_array, string $response_error_msg) {
        $failure_message = "POST Response is ".json_encode($response_as_array);
        $this->assertEquals(self::$ERROR_ENTRY_ID, $response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertStringContainsString($response_error_msg, $response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
    }

    private function assertEntryBaseProperties(array $generated_entry_data, array $entry_response, string $failure_message) {
        $base_properties = ['entry_date', 'entry_value', 'memo', 'expense', 'confirm', 'account_type_id'];
        foreach ($base_properties as $property) {
            $this->assertEquals($entry_response[$property], $generated_entry_data[$property], $failure_message);
        }
    }

}
