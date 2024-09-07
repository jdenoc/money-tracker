<?php

namespace Tests\Feature\Api\Delete;

use App\Models\Account;
use App\Models\AccountType;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class DeleteAccountTypeTest extends TestCase {

    // uri
    private const ACCOUNT_TYPE_URI = '/api/account-type/%d';

    public function testDisableAccountTypeThatDoesNotExist() {
        // GIVEN - account_type does not exist
        $account_type_id = fake()->randomNumber();

        // WHEN
        $response = $this->delete(sprintf(self::ACCOUNT_TYPE_URI, $account_type_id));

        // THEN
        // confirm there are no database records
        $account_type_collection = AccountType::withTrashed()->get();
        $this->assertTrue($account_type_collection->isEmpty(), $account_type_collection->toJson());

        // confirm we got the right response
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testDisabledAccountType() {
        // GIVEN
        $generated_account_type = AccountType::factory()->create();

        // WHEN
        $get_response1 = $this->get(sprintf(self::ACCOUNT_TYPE_URI, $generated_account_type->id));
        $disabled_response = $this->delete(sprintf(self::ACCOUNT_TYPE_URI, $generated_account_type->id));
        $get_response2 = $this->get(sprintf(self::ACCOUNT_TYPE_URI, $generated_account_type->id));

        // THEN
        $this->assertResponseStatus($get_response1, HttpStatus::HTTP_OK);
        $this->assertResponseStatus($disabled_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertResponseStatus($get_response2, HttpStatus::HTTP_OK);

        $get_response1_as_array = $get_response1->json();
        $error_message = $get_response1->getContent();
        $this->assertNotEmpty($get_response1_as_array, $error_message);
        $this->assertArrayHasKey('active', $get_response1_as_array, $error_message);
        $this->assertTrue($get_response1_as_array['active'], $error_message);
        $this->assertArrayHasKey('disabled_stamp', $get_response1_as_array, $error_message);
        $this->assertNull($get_response1_as_array['disabled_stamp'], $error_message);

        $this->assertEmpty($disabled_response->getContent());

        $get_response2_as_array = $get_response2->json();
        $this->assertNotEmpty($get_response2_as_array, $get_response2->getContent());
        $this->assertArrayHasKey('active', $get_response2_as_array, $error_message);
        $this->assertFalse($get_response2_as_array['active'], $error_message);
        $this->assertArrayHasKey('disabled_stamp', $get_response2_as_array, $error_message);
        $this->assertNotNull($get_response2_as_array['disabled_stamp'], $error_message);
    }

}
