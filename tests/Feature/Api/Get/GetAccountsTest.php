<?php

namespace Tests\Feature\Api\Get;

use App\Models\Account;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetAccountsTest extends TestCase {

    // uri
    private string $_uri = '/api/accounts';

    public function testGetListOfAccountsWhenTheyAreAvailable() {
        // GIVEN
        $account_count = fake()->randomDigitNotZero();
        $generated_accounts = Account::factory()->count($account_count)->create();
        // These nodes are not in the response output. Let's hide them from the object collection.
        $generated_accounts->makeHidden([Account::UPDATED_AT, Account::CREATED_AT, Account::DELETED_AT]);

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();

        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($account_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);

        $expected_array_keys = ['id', 'name', 'institution_id', 'active', 'total', 'currency'];
        foreach ($response_body_as_array as $account_in_response) {
            $this->assertEqualsCanonicalizing($expected_array_keys, array_keys($account_in_response));
            $generated_account = $generated_accounts->where('id', $account_in_response['id'])->first();
            $this->assertNotEmpty($generated_account);
            $this->assertEquals($generated_account->toArray(), $account_in_response);
        }
    }

    public function testGetListOfAccountsWhenNoAccountsAreAvailable() {
        // GIVEN - nothing. there should be no data in database

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertIsArray($response_body_as_array);
        $this->assertEmpty($response_body_as_array);
    }

}
