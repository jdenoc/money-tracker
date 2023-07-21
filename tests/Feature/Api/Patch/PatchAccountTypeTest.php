<?php

namespace Tests\Feature\Api\Patch;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Institution;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PatchAccountTypeTest extends TestCase {

    private const PLACEHOLDER_URI_ACCOUNT_TYPE = '/api/account-type/%d';

    public function testRestoringAccountTypeThatDoesNotExist() {
        // GIVEN - no account exists
        $account_type_id = fake()->randomNumber();

        // WHEN
        $response = $this->patch(sprintf(self::PLACEHOLDER_URI_ACCOUNT_TYPE, $account_type_id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testRestoringUndeletedAccountType() {
        // GIVEN
        $generated_account_type = AccountType::factory()
            ->for(Account::factory()->for(Institution::factory()))
            ->create();

        // WHEN
        $response = $this->patch(sprintf(self::PLACEHOLDER_URI_ACCOUNT_TYPE, $generated_account_type->id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testRestoringAccountType() {
        // GIVEN
        $generated_account_type = AccountType::factory()
            ->for(Account::factory()->for(Institution::factory()))
            ->disabled()
            ->create();
        $account_type_uri = sprintf(self::PLACEHOLDER_URI_ACCOUNT_TYPE, $generated_account_type->id);

        // WHEN
        $get_response1 = $this->get($account_type_uri);

        // THEN
        $this->assertResponseStatus($get_response1, HttpStatus::HTTP_OK);
        $this->assertNotEmpty($get_response1->getContent());
        $this->assertFalse($get_response1->json('active'));
        $this->assertNotNull($get_response1->json(AccountType::DELETED_AT));

        // WHEN
        $patch_response = $this->patch($account_type_uri);

        // THEN
        $this->assertResponseStatus($patch_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($patch_response->getContent());

        // WHEN
        $get_response2 = $this->get($account_type_uri);

        // THEN
        $this->assertResponseStatus($get_response2, HttpStatus::HTTP_OK);
        $this->assertNotEmpty($get_response2->getContent());
        $this->assertTrue($get_response2->json('active'));
        $this->assertNull($get_response2->json(AccountType::DELETED_AT));
    }

}
