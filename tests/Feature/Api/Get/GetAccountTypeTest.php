<?php

namespace Tests\Feature\Api\Get;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Institution;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetAccountTypeTest extends TestCase {

    private string $_base_uri = '/api/account-type/%d';

    public function testGetAccountTypeDataWhenNoAccountTypeDataExists() {
        // GIVEN - no database records are created

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, fake()->randomDigitNotZero()));

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetAccountTypeData() {
        // GIVEN
        /** @var AccountType $generated_account_type */
        $generated_account_type = AccountType::factory()
            ->for(Account::factory()->for(Institution::factory()))
            ->create();

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_account_type->id));

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertAccountTypeDetailsOK($response_body_as_array, $generated_account_type);
    }

    /**
     * @param array       $response_as_array
     * @param AccountType $generated_account_type
     */
    private function assertAccountTypeDetailsOK(array $response_as_array, $generated_account_type) {
        $expected_elements = ['id', 'name', 'account_id', 'active', 'type', 'last_digits', AccountType::CREATED_AT, AccountType::UPDATED_AT, AccountType::DELETED_AT];
        $this->assertEqualsCanonicalizing($expected_elements, array_keys($response_as_array));
        foreach ($expected_elements as $element) {
            switch($element) {
                case 'active':
                    $this->assertIsBool($response_as_array[$element]);
                    $this->assertEquals($generated_account_type->$element, $response_as_array[$element]);
                    break;
                case AccountType::CREATED_AT:
                case AccountType::UPDATED_AT:
                    $this->assertDateFormat($response_as_array[$element], DATE_ATOM, $response_as_array[$element]." not in correct format");
                    break;
                case AccountType::DELETED_AT:
                    $this->assertArrayHasKey($element, $response_as_array);
                    if ($response_as_array['active']) {
                        $this->assertNull($response_as_array[$element]);
                    } else {
                        $this->assertDateFormat($response_as_array[$element], DATE_ATOM, $response_as_array[$element]." not in correct format");
                    }
                    break;
                default:
                    $this->assertEquals($generated_account_type->$element, $response_as_array[$element]);
                    break;
            }
        }
    }

}
