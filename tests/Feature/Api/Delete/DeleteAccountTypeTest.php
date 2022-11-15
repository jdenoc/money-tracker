<?php

namespace Tests\Feature\Api\Delete;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class DeleteAccountTypeTest extends TestCase {
    use WithFaker;

    private const PLACEHOLDER_URI_ACCOUNT='/api/account/%d';
    private const PLACEHOLDER_URI_ACCOUNT_TYPE='/api/account-type/%d';

    public function testDisablingAccountTypeThatDoesNotExist() {
        // GIVEN - account_type does not exist
        $account_type_id = $this->faker->randomNumber();

        // WHEN
        $response = $this->delete(sprintf(self::PLACEHOLDER_URI_ACCOUNT_TYPE, $account_type_id));

        // THEN
        // confirm there are no database records
        $account_type_collection = AccountType::all();
        $this->assertTrue($account_type_collection->isEmpty(), $account_type_collection->toJson());

        // confirm we got the right response
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testDisablingAccountType() {
        // GIVEN
        $generated_account = Account::factory()->create();
        $generated_account_type = AccountType::factory()->for($generated_account)->create(['disabled'=>false, 'disabled_stamp'=>null]);
        $account_uri = sprintf(self::PLACEHOLDER_URI_ACCOUNT, $generated_account->id);
        $account_type_uri = sprintf(self::PLACEHOLDER_URI_ACCOUNT_TYPE, $generated_account_type->id);

        // confirm account type is NOT disabled
        // WHEN
        $account_type_response1 = $this->get($account_type_uri);
        $account_response1 = $this->get($account_uri);

        // THEN
        $this->assertResponseStatus($account_type_response1, HttpStatus::HTTP_OK);
        $this->assertResponseStatus($account_response1, HttpStatus::HTTP_OK);

        $account_type_response1_as_array = $account_type_response1->json();
        $error_msg = "Content: ".$account_type_response1->getContent();
        $this->assertNotEmpty($account_type_response1_as_array, $error_msg);
        $this->assertArrayHasKey('disabled', $account_type_response1_as_array, $error_msg);
        $this->assertFalse($account_type_response1_as_array['disabled'], $error_msg);
        $this->assertArrayHasKey('disabled_stamp', $account_type_response1_as_array, $error_msg);
        $this->assertNull($account_type_response1_as_array['disabled_stamp'], $error_msg);

        $account_response1_as_array = $account_response1->json();
        $error_msg = "Content: ".$account_response1->getContent();
        $this->assertNotEmpty($account_response1_as_array, $error_msg);
        $this->assertArrayHasKey('account_types', $account_response1_as_array, $error_msg);
        $this->assertTrue(is_array($account_response1_as_array['account_types']), $error_msg);
        $this->assertNotEmpty($account_response1_as_array['account_types'], $error_msg);
        $this->assertCount(1, $account_response1_as_array['account_types'], "We only created 1 account_type, why has this happened\n".$error_msg);
        foreach ($account_response1_as_array['account_types'] as $account_type_in_response) {
            $this->assertTrue(is_array($account_type_in_response), $error_msg);
            $this->assertArrayHasKey('disabled', $account_type_in_response, $error_msg);
            $this->assertFalse($account_type_in_response['disabled'], $error_msg);
        }

        // disable account-type
        // WHEN
        $disabled_response = $this->delete($account_type_uri);

        // THEN
        $this->assertResponseStatus($disabled_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($disabled_response->getContent());

        // confirm account type is disabled
        // WHEN
        $account_type_response2 = $this->get($account_type_uri);
        $account_response2 = $this->get($account_uri);

        // THEN
        $this->assertResponseStatus($account_type_response2, HttpStatus::HTTP_OK);
        $this->assertResponseStatus($account_response2, HttpStatus::HTTP_OK);

        $account_type_response2_as_array = $account_type_response2->json();
        $error_msg = "Content: ".$account_type_response2->getContent();
        $this->assertNotEmpty($account_type_response2_as_array, $error_msg);
        $this->assertArrayHasKey('disabled', $account_type_response2_as_array, $error_msg);
        $this->assertTrue($account_type_response2_as_array['disabled'], $error_msg);
        $this->assertArrayHasKey('disabled_stamp', $account_type_response2_as_array, $error_msg);
        $this->assertNotNull($account_type_response2_as_array['disabled_stamp'], $error_msg);
        $this->assertDatetimeWithinOneSecond(now()->toAtomString(), $account_type_response2_as_array['disabled_stamp'], $error_msg);

        $account_response2_as_array = $account_response2->json();
        $error_msg = "Content: ".$account_response2->getContent();
        $this->assertNotEmpty($account_response2_as_array, $error_msg);
        $this->assertArrayHasKey('account_types', $account_response2_as_array, $error_msg);
        $this->assertTrue(is_array($account_response2_as_array['account_types']), $error_msg);
        $this->assertNotEmpty($account_response2_as_array['account_types'], $error_msg);
        $this->assertCount(1, $account_response2_as_array['account_types'], "We only created 1 account_type, why has this happened\n".$error_msg);
        foreach ($account_response2_as_array['account_types'] as $account_type_in_response) {
            $this->assertTrue(is_array($account_type_in_response), $error_msg);
            $this->assertArrayHasKey('disabled', $account_type_in_response, $error_msg);
            $this->assertTrue($account_type_in_response['disabled'], $error_msg);
        }
    }

}
