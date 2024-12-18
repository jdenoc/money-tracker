<?php

namespace Tests\Feature\Api\Delete;

use App\Models\Account;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class DeleteAccountTest extends TestCase {
    use WithFaker;

    // uri
    private const PLACEHOLDER_URI_ACCOUNT = '/api/account/%d';

    public function testDisablingAccountThatDoesNotExist() {
        // GIVEN - account_type does not exist
        $account_id = fake()->randomNumber();
        // confirm account does not exist
        $dummy_account = Account::find($account_id);
        $this->assertNull($dummy_account);

        // WHEN
        $response = $this->delete(sprintf(self::PLACEHOLDER_URI_ACCOUNT, $account_id));

        // THEN
        // confirm we got the right response
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testDisablingAccountThatIsAlreadyDisabled() {
        // GIVEN
        $generated_account = Account::factory()->disabled()->create();

        // WHEN
        $response = $this->delete(sprintf(self::PLACEHOLDER_URI_ACCOUNT, $generated_account->id));

        // THEN
        // confirm we got the right response
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testDisablingAccount() {
        // GIVEN
        $generated_account = Account::factory()->create();
        $account_uri = sprintf(self::PLACEHOLDER_URI_ACCOUNT, $generated_account->id);

        // confirm account is NOT disabled
        // WHEN
        $account_response1 = $this->get($account_uri);

        // THEN
        $this->assertResponseStatus($account_response1, HttpStatus::HTTP_OK);
        $account_response1_as_array = $account_response1->json();
        $error_msg = "Content:".$account_response1->getContent();
        $this->assertNotEmpty($account_response1_as_array, $error_msg);
        $this->assertArrayHasKey('active', $account_response1_as_array, $error_msg);
        $this->assertTrue($account_response1_as_array['active'], $error_msg);
        $this->assertArrayHasKey(Account::DELETED_AT, $account_response1_as_array, $error_msg);
        $this->assertNull($account_response1_as_array[Account::DELETED_AT], $error_msg);

        // disable account
        // WHEN
        $disabled_response = $this->delete($account_uri);

        // THEN
        $this->assertResponseStatus($disabled_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($disabled_response->getContent());

        // confirm account is disabled
        // WHEN
        $account_response2 = $this->get($account_uri);

        // THEN
        $this->assertResponseStatus($account_response2, HttpStatus::HTTP_OK);
        $account_response2_as_array = $account_response2->json();
        $error_msg = "Content:".$account_response2->getContent();
        $this->assertNotEmpty($account_response2_as_array, $error_msg);
        $this->assertArrayHasKey('active', $account_response2_as_array, $error_msg);
        $this->assertFalse($account_response2_as_array['active'], $error_msg);
        $this->assertArrayHasKey(Account::DELETED_AT, $account_response1_as_array, $error_msg);
        $this->assertNotNull($account_response2_as_array[Account::DELETED_AT], $error_msg);
        $this->assertDatetimeWithinOneSecond(now()->toAtomString(), $account_response2_as_array[Account::DELETED_AT], $error_msg);
    }

}
