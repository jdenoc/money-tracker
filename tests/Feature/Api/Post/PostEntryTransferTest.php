<?php

namespace Tests\Feature\Api\Post;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Attachment;
use App\Models\Entry;
use App\Models\Tag;
use App\Traits\EntryResponseKeys;
use App\Traits\EntryTransferKeys;
use App\Traits\Tests\StorageTestFiles;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Http\Response as HttpStatus;

class PostEntryTransferTest extends TestCase {
    use EntryTransferKeys;
    use EntryResponseKeys;
    use StorageTestFiles;

    // flags
    const FLAG_HAS_TAGS = 'has_tags';
    const FLAG_HAS_ATTACHMENTS = 'has_attachments';
    const FLAG_OVERRIDE_TO = "override_to_account_type_id";
    const FLAG_OVERRIDE_FROM = "override_from_account_type_id";

    // uri
    private string $_base_uri = '/api/entry/transfer';

    public function testCreateEntryTransferWithoutData() {
        // GIVEN
        $entry_data = [];

        // WHEN
        $response = $this->postJson($this->_base_uri, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $this->assertPostResponseHasCorrectKeys($response->json());
        $this->assertFailedPostResponse($response->json(), self::$ERROR_MSG_SAVE_ENTRY_NO_DATA);
    }

    public static function providerCreateEntryTransferWithMissingData(): array {
        $required_transfer_fields = self::getRequiredTransferFields();
        $test_cases = [];

        // provide data that is missing one property
        foreach ($required_transfer_fields as $required_transfer_field) {
            $test_cases[$required_transfer_field] = ['missing_properties' => [$required_transfer_field]];
        }

        // provide data that is missing two or more properties, but 1 less than the total properties
        $random_missing_properties = array_rand(array_flip($required_transfer_fields), mt_rand(2, count($required_transfer_fields) - 1));
        $test_cases['missing:'.json_encode($random_missing_properties)] = ['missing_properties' => $random_missing_properties];

        return $test_cases;
    }

    /**
     * @dataProvider providerCreateEntryTransferWithMissingData
     */
    public function testCreateEntryTransferWithMissingData(array $missing_properties) {
        // GIVEN
        $transfer_data = $this->generateTransferData();
        $transfer_data = array_diff_key($transfer_data, array_flip($missing_properties));

        $account = Account::factory()->create();
        if (isset($transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE])) {
            $account_type1 = AccountType::factory()->for($account)->create();
            $transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE] = $account_type1->id;
        }
        if (isset($transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE])) {
            $account_type2 = AccountType::factory()->for($account)->create();
            $transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE] = $account_type2->id;
        }

        // WHEN
        $response = $this->postJson($this->_base_uri, $transfer_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $this->assertPostResponseHasCorrectKeys($response->json());
        $this->assertFailedPostResponse($response->json(), sprintf(self::$ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode($missing_properties)));
    }

    public static function providerCreatingEntryTransferWithInvalidAccountType(): array {
        $invalid_account_type_id_transfer_data = [];
        foreach (self::getAccountIdOverrideOptions() as $account_type_id_override_option) {
            $invalid_account_type_id_transfer_data["Invalid account_type:".json_encode($account_type_id_override_option)] = [
                $account_type_id_override_option,
            ];
        }
        return $invalid_account_type_id_transfer_data;
    }

    /**
     * @dataProvider providerCreatingEntryTransferWithInvalidAccountType
     */
    public function testCreatingEntryTransferWithInvalidAccountType($override_account_type_id) {
        // GIVEN
        $transfer_data = $this->generateTransferData();
        $account_type = AccountType::factory()
            ->for(Account::factory())
            ->create();
        if ($override_account_type_id[self::FLAG_OVERRIDE_TO]) {
            $transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE] = $account_type->id;
        }
        if ($override_account_type_id[self::FLAG_OVERRIDE_FROM]) {
            $account_type[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE] = $account_type->id;
        }

        // WHEN
        $response = $this->postJson($this->_base_uri, $transfer_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $this->assertPostResponseHasCorrectKeys($response->json());
        $this->assertFailedPostResponse($response->json(), self::$ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE);
    }

    public function testCreateEntryTransferWithOnlyExternalAccountTypeIds() {
        // GIVEN - $transfer_data
        $transfer_data = $this->generateTransferData();
        $transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE] = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
        $transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE] = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;

        // WHEN
        $response = $this->postJson($this->_base_uri, $transfer_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_BAD_REQUEST);
        $this->assertPostResponseHasCorrectKeys($response->json());
        $this->assertFailedPostResponse($response->json(), self::$ERROR_MSG_SAVE_TRANSFER_BOTH_EXTERNAL);
    }

    public static function providerCreateEntryTransfer(): array {
        $valid_transfer_data = [];
        foreach (self::getAccountIdOverrideOptions() as $account_type_id_override_option) {
            $valid_transfer_data["external account_type ID:".json_encode($account_type_id_override_option)] = [
                $account_type_id_override_option,
            ];
        }
        return $valid_transfer_data;
    }

    /**
     * @dataProvider providerCreateEntryTransfer
     */
    public function testCreateEntryTransfer($remain_external_account_type_id) {
        $non_external_account_counter = 0;
        // GIVEN
        $transfer_data = $this->generateTransferData();
        $transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE] = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
        $transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE] = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;

        $account = Account::factory()->create();
        if (!$remain_external_account_type_id[self::FLAG_OVERRIDE_TO]) {
            $account_type = AccountType::factory()->for($account)->create();
            $transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE] = $account_type->id;
            $non_external_account_counter++;
        }
        if (!$remain_external_account_type_id[self::FLAG_OVERRIDE_FROM]) {
            $account_type = AccountType::factory()->for($account)->create();
            $transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE] = $account_type->id;
            $non_external_account_counter++;
        }

        // WHEN
        $response = $this->postJson($this->_base_uri, $transfer_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_CREATED);
        $response_as_array = $response->json();
        $failure_message = "Response:".$response->getContent();
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertEquals(self::$ERROR_MSG_SAVE_ENTRY_NO_ERROR, $response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertTrue(is_array($response_as_array[self::$RESPONSE_SAVE_KEY_ID]), $failure_message);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertCount($non_external_account_counter, $response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);

        foreach ($response_as_array[self::$RESPONSE_SAVE_KEY_ID] as $entry_id) {
            $get_entry_response = $this->get(str_replace('transfer', $entry_id, $this->_base_uri));
            $this->assertResponseStatus($get_entry_response, HttpStatus::HTTP_OK);
            $failure_message = "Transfer data:".print_r($transfer_data, true)."\nGet Response:".$get_entry_response->getContent()."\n";
            $get_entry_response_as_array = $get_entry_response->json();
            $this->assertEquals($transfer_data['entry_date'], $get_entry_response_as_array['entry_date'], $failure_message);
            $this->assertEquals($transfer_data['entry_value'], $get_entry_response_as_array['entry_value'], $failure_message);
            $this->assertEquals($transfer_data['memo'], $get_entry_response_as_array['memo'], $failure_message);
            if ($get_entry_response_as_array['expense'] == 1) {
                $this->assertEquals($transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE], $get_entry_response_as_array['account_type_id'], $failure_message);
            } elseif ($get_entry_response_as_array['expense'] == 0) {
                $this->assertEquals($transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE], $get_entry_response_as_array['account_type_id'], $failure_message);
            } else {
                $this->fail("Entry Expense value returned is not valid. Response:".$get_entry_response->getContent());
            }
            $this->assertNotNull($get_entry_response_as_array['transfer_entry_id'], $failure_message);
            if ($non_external_account_counter > 1) {
                $this->assertTrue(in_array($get_entry_response_as_array['transfer_entry_id'], $response_as_array[self::$RESPONSE_SAVE_KEY_ID]), $failure_message);
            } else {
                $this->assertEquals(self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID, $get_entry_response_as_array['transfer_entry_id'], $failure_message);
            }
        }
    }

    public static function providerCreateEntryTransferWithTagsAndAttachments(): array {
        return [
            [[self::FLAG_HAS_TAGS => false, self::FLAG_HAS_ATTACHMENTS => false]],  // this is already tested, but for competition’s sake, lets include it
            [[self::FLAG_HAS_TAGS => false, self::FLAG_HAS_ATTACHMENTS => true]],
            [[self::FLAG_HAS_TAGS => true, self::FLAG_HAS_ATTACHMENTS => false]],
            [[self::FLAG_HAS_TAGS => true, self::FLAG_HAS_ATTACHMENTS => true]],
        ];
    }

    /**
     * @dataProvider providerCreateEntryTransferWithTagsAndAttachments
     * @param array $flags
     */
    public function testCreateEntryTransferWithTagsAndAttachments(array $flags) {
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type1 = AccountType::factory()->for($generated_account)->create();
        $generated_account_type2 = AccountType::factory()->for($generated_account)->create();
        $transfer_data = $this->generateTransferData();

        $transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE] = $generated_account_type1->id;
        $transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE] = $generated_account_type2->id;

        $generated_tag_ids = [];
        if ($flags[self::FLAG_HAS_TAGS]) {
            $generate_tag_count = 3;
            $generated_tags = Tag::factory()->count($generate_tag_count)->create();
            $generated_tag_ids = $generated_tags->pluck('id')->toArray();
            $transfer_data['tags'] = $generated_tag_ids;
        }

        if ($flags[self::FLAG_HAS_ATTACHMENTS]) {
            $generated_attachments = Attachment::factory()->count(fake()->randomDigitNotZero())->make();

            $transfer_data['attachments'] = [];
            foreach ($generated_attachments as $generated_attachment) {
                $generated_attachment->storage_store(
                    Storage::disk(self::$TEST_STORAGE_DISK_NAME)->get($this->getTestStorageFileAttachmentFilePathFromFilename($generated_attachment->name)),
                    true
                );
                $transfer_data['attachments'][] = [
                    'uuid' => $generated_attachment->uuid,
                    'name' => $generated_attachment->name,
                ];
            }
        }

        // WHEN
        $post_response = $this->postJson($this->_base_uri, $transfer_data);

        // THEN
        $this->assertResponseStatus($post_response, HttpStatus::HTTP_CREATED);
        $failure_message = "Response is ".$post_response->getContent();
        $post_response_as_array = $post_response->json();
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEquals(self::$ERROR_MSG_SAVE_ENTRY_NO_ERROR, $post_response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertTrue(is_array($post_response_as_array[self::$RESPONSE_SAVE_KEY_ID]), $failure_message);
        $this->assertNotEmpty($post_response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertCount(2, $post_response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);

        foreach ($post_response_as_array[self::$RESPONSE_SAVE_KEY_ID] as $created_entry_id) {
            // WHEN
            $get_entry_response = $this->get(str_replace('transfer', $created_entry_id, $this->_base_uri));

            // THEN
            $this->assertResponseStatus($get_entry_response, HttpStatus::HTTP_OK);
            $failure_message = "Transfer data:".print_r($transfer_data, true)."\nGet Response:".$get_entry_response->getContent()."\n";
            $get_entry_response_as_array = $get_entry_response->json();
            $this->assertEquals($transfer_data['entry_date'], $get_entry_response_as_array['entry_date'], $failure_message);
            $this->assertEquals($transfer_data['entry_value'], $get_entry_response_as_array['entry_value'], $failure_message);
            $this->assertEquals($transfer_data['memo'], $get_entry_response_as_array['memo'], $failure_message);
            if ($get_entry_response_as_array['expense'] == 1) {
                $this->assertEquals($transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE], $get_entry_response_as_array['account_type_id'], $failure_message);
            } elseif ($get_entry_response_as_array['expense'] == 0) {
                $this->assertEquals($transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE], $get_entry_response_as_array['account_type_id'], $failure_message);
            } else {
                $this->fail("Entry Expense value returned is not valid. Response:".$get_entry_response->getContent());
            }
            $this->assertNotNull($get_entry_response_as_array['transfer_entry_id'], $failure_message);
            $this->assertTrue(in_array($get_entry_response_as_array['transfer_entry_id'], $post_response_as_array[self::$RESPONSE_SAVE_KEY_ID]), $failure_message);

            if ($flags[self::FLAG_HAS_TAGS]) {
                // check tags are set
                $this->assertTrue(is_array($get_entry_response_as_array['tags']), $failure_message);
                $this->assertNotEmpty($get_entry_response_as_array['tags'], $failure_message);
                foreach ($get_entry_response_as_array['tags'] as $entry_tag) {
                    $this->assertContains(
                        $entry_tag['id'],
                        $generated_tag_ids,
                        "Generated tag IDs: ".print_r($generated_tag_ids, true)."\nEntry tags in response:".json_encode($get_entry_response_as_array['tags'])
                    );
                }
            }

            if ($flags[self::FLAG_HAS_ATTACHMENTS]) {
                // check attachments were set
                $this->assertArrayHasKey('attachments', $get_entry_response_as_array, $failure_message);
                $this->assertTrue(is_array($get_entry_response_as_array['attachments']), $failure_message);
                $this->assertNotEmpty($get_entry_response_as_array['attachments'], $failure_message);

                foreach ($get_entry_response_as_array['attachments'] as $attachment) {
                    // We json_encode() the $transfer_data['attachments'] array because assertContains() can't traverse multidimensional arrays
                    // We don't check if the UUID value matches because it changes during processing
                    $this->assertStringContainsString($attachment['name'], json_encode($transfer_data['attachments']), $failure_message.'Generated attachments:'.json_encode($transfer_data['attachments']));
                }
            }
        }
    }

    private function generateTransferData(): array {
        $entry_data = Entry::factory()->make();
        return [
            'entry_date' => $entry_data->entry_date,
            'entry_value' => $entry_data->entry_value,
            'memo' => $entry_data->memo,
            self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE => $entry_data->account_type_id,
            self::$TRANSFER_KEY_TO_ACCOUNT_TYPE => $entry_data->account_type_id,
        ];
    }

    private static function getRequiredTransferFields(): array {
        $required_transfer_fields = Entry::get_fields_required_for_creation();
        unset(
            $required_transfer_fields[array_search('account_type_id', $required_transfer_fields)],
            $required_transfer_fields[array_search('expense', $required_transfer_fields)],
            $required_transfer_fields[array_search('confirm', $required_transfer_fields)]
        );
        return array_merge($required_transfer_fields, [self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE, self::$TRANSFER_KEY_TO_ACCOUNT_TYPE]);
    }

    private function assertPostResponseHasCorrectKeys(array $response_as_array): void {
        $failure_message = "Response is ".json_encode($response_as_array);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_SAVE_KEY_ERROR, $response_as_array, $failure_message);
    }

    private function assertFailedPostResponse(array $response_as_array, string $response_error_msg): void {
        $failure_message = "Response is ".json_encode($response_as_array);
        $this->assertTrue(is_array($response_as_array[self::$RESPONSE_SAVE_KEY_ID]), $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertStringContainsString($response_error_msg, $response_as_array[self::$RESPONSE_SAVE_KEY_ERROR], $failure_message);
    }

    private static function getAccountIdOverrideOptions(): array {
        return [
            [self::FLAG_OVERRIDE_TO => false, self::FLAG_OVERRIDE_FROM => false],
            [self::FLAG_OVERRIDE_TO => true, self::FLAG_OVERRIDE_FROM => false],
            [self::FLAG_OVERRIDE_TO => false, self::FLAG_OVERRIDE_FROM => true],
        ];
    }

}
