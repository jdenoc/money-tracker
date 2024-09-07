<?php

namespace Tests\Feature\Api\Get;

use App\Models\AccountType;
use App\Helpers\DatabaseFactoryConstants;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class GetAccountTypesTest extends TestCase {

    // uri
    private string $_uri = '/api/account-types';

    public function testGetAccountTypesWhenNoAccountTypesExist() {
        // GIVEN - no account_types exist

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $this->assertIsArray($response->json());
        $this->assertEmpty($response->json());
    }

    public function testGetAccountTypes() {
        // GIVEN
        $account_type_count = fake()->randomDigitNotZero();
        $generated_account_types = AccountType::factory()
            ->count($account_type_count)
            ->state(new Sequence(function() {
                return [AccountType::DELETED_AT => function() {
                    return fake()->boolean() ? fake()->date(DatabaseFactoryConstants::DATE_FORMAT) : null;
                }];
            }))
            ->create();

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertNotEmpty($response_as_array);

        $this->assertArrayHasKey('count', $response_as_array);
        $this->assertEquals($account_type_count, $response_as_array['count']);
        unset($response_as_array['count']);

        $expected_elements = ['id', 'type', 'last_digits', 'name', 'account_id', 'active', AccountType::CREATED_AT, AccountType::UPDATED_AT, AccountType::DELETED_AT];
        foreach ($response_as_array as $account_type_in_response) {
            $this->assertEqualsCanonicalizing($expected_elements, array_keys($account_type_in_response));
            $generated_account_type = $generated_account_types->where('id', $account_type_in_response['id'])->first();
            $this->assertNotEmpty($generated_account_type);
            $this->assertEquals($generated_account_type->toArray(), $account_type_in_response);
        }
    }

}
