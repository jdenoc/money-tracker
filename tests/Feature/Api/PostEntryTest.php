<?php

namespace Tests\Feature\Api;

use App\Account;
use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Http\Controllers\Api\EntryController;
use App\Tag;
use Faker\Factory as FakerFactory;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PostEntryTest extends TestCase {

    use DatabaseMigrations;

    private $_base_uri = '/api/entry';

    public function testCreateEntryWithoutData(){
        // GIVEN
        $entry_data = [];

        // WHEN
        $response = $this->json('POST', $this->_base_uri, $entry_data);

        // THEN
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response_as_array = $this->getResponseAsArray($response);
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, EntryController::ERROR_MSG_SAVE_ENTRY_NO_DATA);
    }

    public function providerCreateEntryWithMissingData(){
        // PHPUnit data providers are called before setUp() and setUpBeforeClass() are called.
        // With that piece of information, we need to call setUp() earlier than we normally would so that we can use model factories
        //$this->setUp();
        // We can no longer call setUp() as a work around
        // it caused the database to populate and in doing so we caused some tests to fail.
        // Said tests failed because they were testing the absence of database values.
        $this->initialiseApplication();

        $required_entry_fields = Entry::get_fields_required_for_creation();

        $missing_data_entries = [];
        // provide data that is missing one property
        for($i=0; $i<count($required_entry_fields); $i++){
            $entry_data = $this->generateEntryData();
            unset($entry_data[$required_entry_fields[$i]]);
            $missing_data_entries['missing ['.$required_entry_fields[$i].']'] = [
                $entry_data,
                sprintf(EntryController::ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode([$required_entry_fields[$i]]))
            ];
        }

        // provide data that is missing two or more properties, but 1 less than the total properties
        $entry_data = $this->generateEntryData();
        $unset_keys = array_rand($required_entry_fields, mt_rand(2, count($required_entry_fields)-1));
        $removed_keys = [];
        foreach($unset_keys as $unset_key){
            $removed_key = $required_entry_fields[$unset_key];
            unset($entry_data[$removed_key]);
            $removed_keys[] = $removed_key;
        }
        $missing_data_entries['missing ['.implode(',', $removed_keys).']'] = [
            $entry_data,
            sprintf(EntryController::ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode($removed_keys))
        ];

        return $missing_data_entries;
    }

    /**
     * @dataProvider providerCreateEntryWithMissingData
     * @param array $entry_data
     * @param string $expected_response_error_msg
     */
    public function testCreateEntryWithMissingData($entry_data, $expected_response_error_msg){
        // GIVEN - $entry_data by providerCreateEntryWithMissingData

        // WHEN
        $response = $this->json('POST', $this->_base_uri, $entry_data);

        // THEN
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response_as_array = $this->getResponseAsArray($response);
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, $expected_response_error_msg);
    }

    public function testCreateEntryButAccountTypeDoesNotExist(){
        // GIVEN
        $entry_data = $this->generateEntryData();

        // WHEN
        $response = $this->json("POST", $this->_base_uri, $entry_data);

        // THEN
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response_as_array = $this->getResponseAsArray($response);
        $this->assertPostResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPostResponse($response_as_array, EntryController::ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE);
    }

    public function testCreateEntryAndAccountTotalUpdate(){
        // GIVEN
        $generated_account = factory(Account::class)->create();
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$generated_account->id]);
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['account_type'] = $generated_account_type->id;

        // WHEN
        $get_account_response1 = $this->get('/api/account/'.$generated_account->id);
        // THEN
        $get_account_response1->assertStatus(Response::HTTP_OK);
        $get_account_response1_as_array = $this->getResponseAsArray($get_account_response1);
        $this->assertArrayHasKey('total', $get_account_response1_as_array);
        $original_account_total = $get_account_response1_as_array['total'];
        $this->assertArrayHasKey('account_types', $get_account_response1_as_array);
        $this->assertTrue(is_array($get_account_response1_as_array['account_types']));
        $this->assertNotEmpty($get_account_response1_as_array['account_types']);

        // WHEN
        $post_response = $this->json("POST", $this->_base_uri, $generated_entry_data);
        // THEN
        $post_response->assertStatus(Response::HTTP_CREATED);
        $post_response_as_array = $this->getResponseAsArray($post_response);
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEmpty($post_response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR]);
        $created_entry_id = $post_response_as_array[EntryController::RESPONSE_SAVE_KEY_ID];
        $this->assertGreaterThan(EntryController::ERROR_ENTRY_ID, $created_entry_id);

        // WHEN
        $get_entry_response = $this->get($this->_base_uri.'/'.$created_entry_id);
        // THEN
        $get_entry_response->assertStatus(Response::HTTP_OK);
        $get_entry_response_as_array = $this->getResponseAsArray($get_entry_response);
        $this->assertEquals($generated_entry_data['entry_date'], $get_entry_response_as_array['entry_date']);
        $this->assertEquals($generated_entry_data['entry_value'], $get_entry_response_as_array['entry_value']);
        $this->assertEquals($generated_entry_data['memo'], $get_entry_response_as_array['memo']);
        $this->assertEquals($generated_entry_data['expense'], $get_entry_response_as_array['expense']);
        $this->assertEquals($generated_entry_data['confirm'], $get_entry_response_as_array['confirm']);
        $this->assertEquals($generated_entry_data['account_type'], $get_entry_response_as_array['account_type']);

        // WHEN
        $get_account_response2 = $this->get('/api/account/'.$generated_account->id);
        // THEN
        $get_account_response2->assertStatus(Response::HTTP_OK);
        $get_account_response2_as_array = $this->getResponseAsArray($get_account_response2);
        $this->assertArrayHasKey('total', $get_account_response2_as_array);
        $new_account_total = $get_account_response2_as_array['total'];

        $entry_value = (($generated_entry_data['expense']) ? -1 : 1)*$generated_entry_data['entry_value'];
        $this->assertEquals($original_account_total+$entry_value, $new_account_total);
    }

    public function testCreateEntryButTagDoesNotExist(){
        $faker = FakerFactory::create();
        // GIVEN
        do{
            $generate_tag_count = $faker->randomDigitNotNull;
        }while($generate_tag_count < 3);
        $generated_tags = factory(Tag::class, $generate_tag_count)->create();
        $generated_tag_ids = $generated_tags->pluck('id')->toArray();
        do{
            $non_existent_tag_id = $faker->randomDigitNotNull;
        }while(in_array($non_existent_tag_id, $generated_tag_ids));

        $generated_account = factory(Account::class)->create();
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$generated_account->id]);
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['tags'] = [$non_existent_tag_id];
        $generated_entry_data['tags'] = array_merge($generated_entry_data['tags'], array_rand($generated_tag_ids, 3));
        $generated_entry_data['account_type'] = $generated_account_type->id;

        // WHEN
        $post_response = $this->json('POST', $this->_base_uri, $generated_entry_data);
        // THEN
        $post_response->assertStatus(Response::HTTP_CREATED);
        $post_response_as_array = $this->getResponseAsArray($post_response);
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEmpty($post_response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR]);
        $created_entry_id = $post_response_as_array[EntryController::RESPONSE_SAVE_KEY_ID];
        $this->assertGreaterThan(EntryController::ERROR_ENTRY_ID, $created_entry_id);

        // WHEN
        $get_response = $this->get($this->_base_uri.'/'.$created_entry_id);
        // THEN
        $get_response->assertStatus(Response::HTTP_OK);
        $get_response_as_array = $this->getResponseAsArray($get_response);
        $entry_failure_message = "Generated entry:".$get_response->getContent();
        $this->assertEquals($generated_entry_data['entry_date'], $get_response_as_array['entry_date'], $entry_failure_message);
        $this->assertEquals($generated_entry_data['entry_value'], $get_response_as_array['entry_value'], $entry_failure_message);
        $this->assertEquals($generated_entry_data['memo'], $get_response_as_array['memo'], $entry_failure_message);
        $this->assertEquals($generated_entry_data['expense'], $get_response_as_array['expense'], $entry_failure_message);
        $this->assertEquals($generated_entry_data['confirm'], $get_response_as_array['confirm'], $entry_failure_message);
        $this->assertEquals($generated_entry_data['account_type'], $get_response_as_array['account_type'], $entry_failure_message);
        $this->assertTrue(is_array($get_response_as_array['tags']), $entry_failure_message);
        $this->assertNotEmpty($get_response_as_array['tags'], $entry_failure_message);
        $this->assertFalse(in_array($non_existent_tag_id, $get_response_as_array['tags']), $entry_failure_message);
        foreach($get_response_as_array['tags'] as $entry_tag){
            $this->assertContains(
                $entry_tag['id'],
                $generated_tag_ids,
                "Generated tag IDs: ".json_encode($generated_tag_ids)."\nEntry tags in response:".json_encode($get_response_as_array['tags'])
            );
        }
    }

    public function testCreateEntryWithAttachments(){
        $faker = FakerFactory::create();
        // GIVEN
        do{
            $generated_attachment_count = $faker->randomDigitNotNull;
        }while($generated_attachment_count <= 0);
        $generated_attachments = factory(Attachment::class, $generated_attachment_count)->make();
        $generated_account = factory(Account::class)->create();
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$generated_account->id]);
        $generated_entry_data = $this->generateEntryData();
        $generated_entry_data['account_type'] = $generated_account_type->id;
        $generated_entry_data['attachments'] = [];
        foreach($generated_attachments as $generated_attachment){
            $generated_entry_data['attachments'][] = [
                'uuid'=>$generated_attachment->uuid,
                'attachment'=>$generated_attachment->attachment
            ];
        }

        // WHEN
        $post_response = $this->json("POST", $this->_base_uri, $generated_entry_data);

        // THEN
        $post_response->assertStatus(Response::HTTP_CREATED);
        $post_response_as_array = $this->getResponseAsArray($post_response);
        $this->assertPostResponseHasCorrectKeys($post_response_as_array);
        $this->assertEmpty($post_response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR]);
        $created_entry_id = $post_response_as_array[EntryController::RESPONSE_SAVE_KEY_ID];
        $this->assertGreaterThan(EntryController::ERROR_ENTRY_ID, $created_entry_id);

        // WHEN
        $get_response = $this->get($this->_base_uri.'/'.$created_entry_id);

        // THEN
        $get_response->assertStatus(Response::HTTP_OK);
        $get_response_as_array = $this->getResponseAsArray($get_response);
        $this->assertEquals($generated_entry_data['entry_date'], $get_response_as_array['entry_date']);
        $this->assertEquals($generated_entry_data['entry_value'], $get_response_as_array['entry_value']);
        $this->assertEquals($generated_entry_data['memo'], $get_response_as_array['memo']);
        $this->assertEquals($generated_entry_data['expense'], $get_response_as_array['expense']);
        $this->assertEquals($generated_entry_data['confirm'], $get_response_as_array['confirm']);
        $this->assertEquals($generated_entry_data['account_type'], $get_response_as_array['account_type']);
        $this->assertArrayHasKey('attachments', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['attachments']));
        $this->assertNotEmpty($get_response_as_array['attachments']);
        foreach($get_response_as_array['attachments'] as $attachment){
            $attachment_data = [
                'uuid'=>$attachment['uuid'],
                'attachment'=>$attachment['attachment']
            ];
            $this->assertContains($attachment_data, $generated_entry_data['attachments'], 'Generated attachments:'.json_encode($generated_entry_data['attachments']));
        }
    }

    private function generateEntryData(){
        $entry_data = factory(Entry::class)->make();
        return [
            'account_type'=>$entry_data->account_type,
            'confirm'=>$entry_data->confirm,
            'entry_date'=>$entry_data->entry_date,
            'entry_value'=>$entry_data->entry_value,
            'expense'=>$entry_data->expense,
            'memo'=>$entry_data->memo
        ];
    }

    private function assertPostResponseHasCorrectKeys($response_as_array){
        $failure_message = "POST Response is ".json_encode($response_as_array);
        $this->assertTrue(is_array($response_as_array), $failure_message);
        $this->assertArrayHasKey(EntryController::RESPONSE_SAVE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(EntryController::RESPONSE_SAVE_KEY_ERROR, $response_as_array, $failure_message);
    }

    private function assertFailedPostResponse($response_as_array, $response_error_msg){
        $failure_message = "POST Response is ".json_encode($response_as_array);
        $this->assertEquals(EntryController::ERROR_ENTRY_ID, $response_as_array[EntryController::RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertContains($response_error_msg, $response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR], $failure_message);
    }

}