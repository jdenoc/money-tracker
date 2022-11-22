<?php

namespace Tests\Feature\Api\Patch;

use App\Models\Account;
use App\Models\Institution;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PatchAccountTest extends TestCase {
    use WithFaker;

    private const PLACEHOLDER_URI_ACCOUNT = '/api/account/%d';

    public function testRestoringAccountThatDoesNotExist() {
        // GIVEN - no account exists
        $account_id = $this->faker->randomNumber();

        // WHEN
        $response = $this->patch(sprintf(self::PLACEHOLDER_URI_ACCOUNT, $account_id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testRestoringUndeletedAccount() {
        // GIVEN
        $generated_institution = Institution::factory()->create();
        $generated_account = Account::factory()->create(['institution_id'=>$generated_institution->id, Account::DELETED_AT=>null]);

        // WHEN
        $response = $this->patch(sprintf(self::PLACEHOLDER_URI_ACCOUNT, $generated_account->id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testRestoringAccount() {
        // GIVEN
        $generated_institution = Institution::factory()->create();
        $generated_account = Account::factory()->create(['institution_id'=>$generated_institution->id, Account::DELETED_AT=>now()]);
        $account_uri = sprintf(self::PLACEHOLDER_URI_ACCOUNT, $generated_account->id);

        // WHEN
        $get_response1 = $this->get($account_uri);

        // THEN
        $this->assertResponseStatus($get_response1, HttpStatus::HTTP_OK);
        $this->assertNotEmpty($get_response1->getContent());
        $this->assertFalse($get_response1->json('active'));
        $this->assertNotNull($get_response1->json(Account::DELETED_AT));

        // WHEN
        $patch_response = $this->patch($account_uri);

        // THEN
        $this->assertResponseStatus($patch_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($patch_response->getContent());

        // WHEN
        $get_response2 = $this->get($account_uri);

        // THEN
        $this->assertResponseStatus($get_response2, HttpStatus::HTTP_OK);
        $this->assertNotEmpty($get_response2->getContent());
        $this->assertTrue($get_response2->json('active'));
        $this->assertNull($get_response2->json(Account::DELETED_AT));
    }

}
