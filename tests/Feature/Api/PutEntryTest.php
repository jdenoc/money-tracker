<?php

namespace Tests\Feature\Api;

use App\Account;
use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Http\Controllers\Api\EntryController;
use App\Tag;
use Faker\Factory as FakerFactory;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PutEntryTest extends TestCase {

    private $_base_uri = '/api/entry/';
    private $_generated_account;
    private $_generated_account_type;
    private $_generated_entry;

    public function setUp(){
        parent::setUp();
        // GIVEN - for all tests
        $this->_generated_account = factory(Account::class)->create();
        $this->_generated_account_type = factory(AccountType::class)->create(['account_id'=>$this->_generated_account->id]);
        $this->_generated_entry = factory(Entry::class)->create(['account_type_id'=>$this->_generated_account_type->id]);
    }

    public function testUpdateEntryWithoutProvidingData(){
        // GIVEN - see setUp()
        $update_data = [];

        // WHEN
        $response = $this->json("PUT", $this->_base_uri.$this->_generated_entry->id, $update_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $this->getResponseAsArray($response);
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPutResponse($response_as_array, EntryController::ERROR_MSG_SAVE_ENTRY_NO_DATA);
    }

    public function testUpdateEntryButNewAccountTypeDoesNotExist(){
        $faker = FakerFactory::create();
        // GIVEN - see setUp()

        $entry_data = factory(Entry::class)->make();
        $entry_data = $entry_data->toArray();
        // make sure account_type_id value that does not exist
        while($entry_data['account_type_id'] == $this->_generated_account_type->id){
            $entry_data['account_type_id'] = $faker->randomDigitNotNull;
        }

        // WHEN
        $response = $this->json('PUT', $this->_base_uri.$this->_generated_entry->id, $entry_data);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_BAD_REQUEST);
        $response_as_array = $this->getResponseAsArray($response);
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPutResponse($response_as_array, EntryController::ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE);
    }

    public function testUpdateEntryButEntryDoesNotExist(){
        $faker = FakerFactory::create();
        // GIVEN - entry does not exist
        do{
            // make sure randomly generated entry ID isn't associated
            // with the pre-generated entry
            $entry_id = $faker->randomNumber();
        }while($entry_id == $this->_generated_entry->id);
        $entry_data = factory(Entry::class)->make(['account_type_id'=>$this->_generated_account_type->id]);
        $entry_data = $entry_data->toArray();

        // WHEN
        $response = $this->json('PUT', $this->_base_uri.$entry_id, $entry_data);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $response_as_array = $response->json();
        $this->assertPutResponseHasCorrectKeys($response_as_array);
        $this->assertFailedPutResponse($response_as_array, EntryController::ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST);
    }

    public function testUpdateEntryAndConfirmAccountTotalUpdated(){
        // GIVEN - see setUp()
        $entry_data = factory(Entry::class)->make();
        $entry_data = [
            'entry_value'=>$entry_data->entry_value
        ];

        // WHEN
        $get_account_response1 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account_response1->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response1_as_array = $this->getResponseAsArray($get_account_response1);
        $this->assertArrayHasKey('total', $get_account_response1_as_array);
        $original_total = $get_account_response1_as_array['total'];

        // WHEN
        $put_response = $this->json("PUT", $this->_base_uri.$this->_generated_entry->id, $entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $this->getResponseAsArray($put_response);
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_entry_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_entry_response->assertStatus(HttpStatus::HTTP_OK);
        $get_entry_response_as_array = $this->getResponseAsArray($get_entry_response);
        $this->assertArrayHasKey('entry_value', $get_entry_response_as_array);
        $this->assertEquals($entry_data['entry_value'], $get_entry_response_as_array['entry_value']);

        // WHEN
        $get_account_response2 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account_response2->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response2_as_array = $this->getResponseAsArray($get_account_response2);
        $this->assertArrayHasKey('total', $get_account_response2_as_array);
        $updated_total = $get_account_response2_as_array['total'];

        $original_entry_value = ($this->_generated_entry->expense ? -1 : 1)*$this->_generated_entry->entry_value;
        $new_entry_value = ($get_entry_response_as_array['expense'] ? -1 : 1)*$get_entry_response_as_array['entry_value'];
        $this->assertEquals(
            $original_total-$original_entry_value+$new_entry_value,
            $updated_total,
            "original total:".$original_total."\nOriginal entry value:".$original_entry_value."\nNew entry value:".$new_entry_value."\nNew total:".$updated_total
        );
    }

    public function testUpdateEntryAsDeletedAndConfirmValueRemovedFromAccountTotal(){
        // GIVEN - see setUp()
        $entry_data = [
            'disabled'=>true
        ];

        // WHEN
        $get_account_response1 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account_response1->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response1_as_array = $this->getResponseAsArray($get_account_response1);
        $this->assertArrayHasKey('total', $get_account_response1_as_array);
        $original_total = $get_account_response1_as_array['total'];

        // WHEN
        $put_response = $this->json("PUT", $this->_base_uri.$this->_generated_entry->id, $entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $this->getResponseAsArray($put_response);
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertEmpty($put_response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR]);
        $this->assertGreaterThan(EntryController::ERROR_ENTRY_ID, $put_response_as_array[EntryController::RESPONSE_SAVE_KEY_ID]);

        // WHEN
        $get_entry_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_entry_response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $get_entry_response_as_array = $this->getResponseAsArray($get_entry_response);
        $this->assertTrue(is_array($get_entry_response_as_array));
        $this->assertEmpty($get_entry_response_as_array);

        // WHEN
        $get_account_response2 = $this->get('/api/account/'.$this->_generated_account->id);
        // THEN
        $get_account_response2->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response2_as_array = $this->getResponseAsArray($get_account_response2);
        $this->assertArrayHasKey('total', $get_account_response2_as_array);
        $updated_total = $get_account_response2_as_array['total'];

        $original_entry_value = ($this->_generated_entry->expense ? -1 : 1)*$this->_generated_entry->entry_value;
        $this->assertEquals(
            $original_total-$original_entry_value,
            $updated_total,
            "Original total:".$original_total."\nOriginal entry value:".$original_entry_value."\nNew total:".$updated_total
        );
    }

    public function providerUpdateEntryWithCertainProperties(){
        // PHPUnit data providers are called before setUp() and setUpBeforeClass() are called.
        // With that piece of information, we need to call setUp() earlier than we normally would so that we can use model factories
        //$this->setUp();
        // We can no longer call setUp() as a work around
        // it caused the database to populate and in doing so we caused some tests to fail.
        // Said tests failed because they were testing the absence of database values.
        $this->initialiseApplication();

        $required_entry_fields = Entry::get_fields_required_for_update();
        unset($required_entry_fields[array_search('disabled', $required_entry_fields)]);
        unset($required_entry_fields[array_search('entry_value', $required_entry_fields)]);
        unset($required_entry_fields[array_search('account_type_id', $required_entry_fields)]);
        $required_entry_fields = array_values($required_entry_fields);  // do this to reset array index after unset
        $generated_entry_data1 = factory(Entry::class)->make();
        $generated_entry_data2 = factory(Entry::class)->make();

        $update_entry_data = [];
        for($i=0; $i<count($required_entry_fields); $i++){
            $update_entry_data['update ['.$required_entry_fields[$i].']'] = [
                [$required_entry_fields[$i]=>$generated_entry_data1[$required_entry_fields[$i]]]
            ];
        }

        $update_batch_properties = [];
        $batch_entry_fields = array_rand($required_entry_fields, mt_rand(2, count($required_entry_fields)-1));
        $batch_entry_fields = array_intersect_key($required_entry_fields, array_flip($batch_entry_fields));
        foreach($batch_entry_fields as $entry_property){
            $update_batch_properties[$entry_property] = $generated_entry_data2[$entry_property];
        }
        $update_entry_data['update ['.implode(',', $batch_entry_fields).']'] = [$update_batch_properties];

        return $update_entry_data;
    }

    /**
     * @dataProvider providerUpdateEntryWithCertainProperties
     * @param array $entry_data
     */
    public function testUpdateEntryWithCertainProperties($entry_data){
        // GIVEN - see setUp()

        // WHEN
        $put_response = $this->json("PUT", $this->_base_uri.$this->_generated_entry->id, $entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $this->getResponseAsArray($put_response);
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response->assertStatus(HttpStatus::HTTP_OK);
        $get_response_as_array = $this->getResponseAsArray($get_response);
        foreach($entry_data as $property=>$value){
            $this->assertEquals($value, $get_response_as_array[$property], "Entry data:".json_encode($entry_data)."\nGet Request:".$get_response->getContent());
        }
    }

    public function testUpdateEntryWithTagThatDoesNotExist(){
        // GIVEN - see setUp()
        $faker = FakerFactory::create();
        $generate_tag_count = $faker->numberBetween(2, 5);
        factory(Tag::class, $generate_tag_count)->create();
        $put_entry_data = ['tags'=>Tag::all()->pluck('id')->toArray()];
        do{
            $non_existent_tag_id = $faker->randomDigitNotNull;
        }while(in_array($non_existent_tag_id, $put_entry_data['tags']));
        $put_entry_data['tags'][] = $non_existent_tag_id;

        // WHEN
        $put_response = $this->json('PUT', $this->_base_uri.$this->_generated_entry->id, $put_entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $this->getResponseAsArray($put_response);
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response->assertStatus(HttpStatus::HTTP_OK);
        $get_response_as_array = $this->getResponseAsArray($get_response);
        $this->assertArrayHasKey('tags', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['tags']));
        $this->assertNotEmpty($get_response_as_array['tags']);
        foreach($get_response_as_array['tags'] as $idx=>$response_tag){
            $this->assertContains($response_tag['id'], $put_entry_data['tags']);
            unset($get_response_as_array['tags'][$idx]);
        }
        $this->assertEmpty($get_response_as_array['tags']);
    }

    public function testUpdateEntryWithAttachments(){
        // GIVEN
        $attachment_data = factory(Attachment::class)->make();
        $put_entry_data = ['attachments'=>[[
            'uuid'=>$attachment_data->uuid,
            'name'=>$attachment_data->name
        ]]];

        // WHEN
        $put_response = $this->json("PUT", $this->_base_uri.$this->_generated_entry->id, $put_entry_data);
        // THEN
        $this->assertResponseStatus($put_response, HttpStatus::HTTP_OK);
        $put_response_as_array = $this->getResponseAsArray($put_response);
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $this->getResponseAsArray($get_response);
        $this->assertArrayHasKey('attachments', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['attachments']));
        $this->assertNotEmpty($get_response_as_array['attachments']);
        foreach($get_response_as_array['attachments'] as $response_attachment){
            $attachment_data = [
                'uuid'=>$response_attachment['uuid'],
                'name'=>$response_attachment['name']
            ];
            $this->assertContains($attachment_data, $put_entry_data['attachments'], 'Generated attachments:'.json_encode($put_entry_data['attachments']));
        }
    }

    public function testUpdateEntryWithAttachmentsAndAttachmentsAreAlreadyAttached(){
        // GIVEN
        $unattached_attachment = factory(Attachment::class)->make();
        $put_entry_data = ['attachments'=>[
            ['uuid'=>$unattached_attachment->uuid, 'name'=>$unattached_attachment->name]
        ]];
        $attached_attachments = factory(Attachment::class, 3)->create(['entry_id'=>$this->_generated_entry->id]);
        foreach($attached_attachments as $attached_attachment){
            $put_entry_data['attachments'][] = ['uuid'=>$attached_attachment->uuid, 'name'=>$attached_attachment->name];
        }

        // WHEN
        $put_response = $this->json("PUT", $this->_base_uri.$this->_generated_entry->id, $put_entry_data);

        // THEN
        $this->assertResponseStatus($put_response, HttpStatus::HTTP_OK);
        $put_response_as_array = $this->getResponseAsArray($put_response);
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $get_response_as_array = $this->getResponseAsArray($get_response);
        $this->assertArrayHasKey('attachments', $get_response_as_array);
        $this->assertTrue(is_array($get_response_as_array['attachments']));
        $this->assertNotEmpty($get_response_as_array['attachments']);
        foreach($get_response_as_array['attachments'] as $response_attachment){
            $attachment_data = [
                'uuid'=>$response_attachment['uuid'],
                'name'=>$response_attachment['name']
            ];
            // This step really makes sure that entries have not been duplicated
            $this->assertContains($attachment_data, $put_entry_data['attachments'], 'Generated attachments:'.json_encode($put_entry_data['attachments']));
        }
    }

    public function testUpdateEntryWithNoUpdates(){
        // GIVEN
        $put_entry_data = $this->_generated_entry->toArray();

        // WHEN
        $put_response = $this->json('PUT', $this->_base_uri.$this->_generated_entry->id, $put_entry_data);
        // THEN
        $put_response->assertStatus(HttpStatus::HTTP_OK);
        $put_response_as_array = $this->getResponseAsArray($put_response);
        $this->assertPutResponseHasCorrectKeys($put_response_as_array);
        $this->assertSuccessPutResponse($put_response_as_array);

        // WHEN
        $get_response = $this->get($this->_base_uri.$this->_generated_entry->id);
        // THEN
        $get_response->assertStatus(HttpStatus::HTTP_OK);
        $get_response_as_array = $this->getResponseAsArray($get_response);
        $this->assertEquals($put_entry_data['entry_date'], $get_response_as_array['entry_date'], $get_response->getContent());
        $this->assertEquals($put_entry_data['entry_value'], $get_response_as_array['entry_value'], $get_response->getContent());
        $this->assertEquals($put_entry_data['memo'], $get_response_as_array['memo'], $get_response->getContent());
        $this->assertEquals($put_entry_data['expense'], $get_response_as_array['expense'], $get_response->getContent());
        $this->assertEquals($put_entry_data['confirm'], $get_response_as_array['confirm'], $get_response->getContent());
        $this->assertEquals($put_entry_data['account_type_id'], $get_response_as_array['account_type_id'], $get_response->getContent());
    }

    /**
     * @param array $response_as_array
     */
    private function assertPutResponseHasCorrectKeys($response_as_array){
        $failure_message = "PUT Response is ".json_encode($response_as_array);
        $this->assertTrue(is_array($response_as_array), $failure_message);
        $this->assertArrayHasKey(EntryController::RESPONSE_SAVE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(EntryController::RESPONSE_SAVE_KEY_ERROR, $response_as_array, $failure_message);
    }

    /**
     * @param array $response_as_array
     * @param string $response_error_msg
     */
    private function assertFailedPutResponse($response_as_array, $response_error_msg){
        $failure_message = "PUT response is ".json_encode($response_as_array);
        $this->assertEquals(EntryController::ERROR_ENTRY_ID, $response_as_array[EntryController::RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertContains($response_error_msg, $response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR], $failure_message);
    }

    /**
     * @param array $response_as_array
     */
    private function assertSuccessPutResponse($response_as_array){
        $failure_message = "PUT response is ".json_encode($response_as_array);
        $this->assertEmpty($response_as_array[EntryController::RESPONSE_SAVE_KEY_ERROR], $failure_message);
        $this->assertGreaterThan(EntryController::ERROR_ENTRY_ID, $response_as_array[EntryController::RESPONSE_SAVE_KEY_ID], $failure_message);
        $this->assertEquals($this->_generated_entry->id, $response_as_array[EntryController::RESPONSE_SAVE_KEY_ID], $failure_message." while updating entry ID ".$this->_generated_entry->id);
    }

}