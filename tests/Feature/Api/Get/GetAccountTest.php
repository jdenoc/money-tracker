<?php

namespace Tests\Feature\Api\Get;

use App\Helpers\CurrencyHelper;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetAccountTest extends TestCase {
    use WithFaker;

    private string $_base_uri = '/api/account/%d';

    public function testGetAccountData() {
        // GIVEN
        $account_type_count = $this->faker->randomDigitNotZero();
        /** @var Account $generated_account */
        $generated_account = Account::factory()->create();
        $generated_account_types = AccountType::factory()->count($account_type_count)->for($generated_account)->create();
        // These nodes are not in the response output. Let's hide them from the object collection
        $generated_account_types->makeHidden(['account_id', 'last_updated', AccountType::CREATED_AT, AccountType::UPDATED_AT, AccountType::DELETED_AT]);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_account->id));

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertAccountDetailsOK($response_body_as_array, $generated_account, $account_type_count);
        $this->assertAccountTypesOK($response_body_as_array['account_types'], $generated_account_types);
    }

    public function testGetAccountDataWhenAnAccountTypesRecordIsDisabled() {
        // GIVEN
        $account_type_count = $this->faker->randomDigitNotZero();
        /** @var Account $generated_account */
        $generated_account = Account::factory()->create();
        $generated_account_types = AccountType::factory()->count($account_type_count)->for($generated_account)->create();
        $generated_disabled_account_type = AccountType::factory()->for($generated_account)->create([AccountType::DELETED_AT=>now()]);
        $account_type_count++;
        $generated_account_types->push($generated_disabled_account_type);
        // These nodes are not in the response output. Let's hide them from the object collection
        $generated_account_types->makeHidden(['account_id', 'last_updated', AccountType::CREATED_AT, AccountType::UPDATED_AT, AccountType::DELETED_AT]);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_account->id));

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertAccountDetailsOK($response_body_as_array, $generated_account, $account_type_count);
        $this->assertAccountTypesOK($response_body_as_array['account_types'], $generated_account_types);
    }

    public function testGetAccountDataWhenNoAccountTypeRecordsExist() {
        // GIVEN
        /** @var Account $generated_account */
        $generated_account = Account::factory()->create();

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_account->id));

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $this->assertAccountDetailsOK($response->json(), $generated_account, 0);
    }

    public function testGetAccountDataWhenOnlyDisabledAccountTypeRecordsExist() {
        // GIVEN
        $account_type_count = $this->faker->randomDigitNotZero();
        /** @var Account $generated_account */
        $generated_account = Account::factory()->create();
        AccountType::factory()->count($account_type_count)->for($generated_account)->create([AccountType::DELETED_AT=>now()]);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_account->id));

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $this->assertAccountDetailsOK($response->json(), $generated_account, $account_type_count);
    }

    public function testGetAccountDataWhenNoAccountDataExists() {
        // GIVEN - no database records are created

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $this->faker->randomDigitNotZero()));

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    /**
     * @param array $response_as_array
     * @param Account $generated_account
     * @param int $account_type_count
     */
    private function assertAccountDetailsOK(array $response_as_array, $generated_account, int $account_type_count) {
        $expected_elements = ['id', 'name', 'institution_id', 'active', 'total', 'currency', 'account_types', Account::CREATED_AT, Account::UPDATED_AT, Account::DELETED_AT];
        $this->assertEqualsCanonicalizing($expected_elements, array_keys($response_as_array));
        foreach ($expected_elements as $expected_element) {
            switch ($expected_element) {
                case 'currency':
                    $this->assertEquals(3, strlen($response_as_array[$expected_element]));
                    $this->assertTrue(in_array($response_as_array[$expected_element], CurrencyHelper::getCodesAsArray()));
                    $this->assertEquals($generated_account->$expected_element, $response_as_array[$expected_element]);
                    break;
                case 'account_types':
                    $this->assertIsArray($response_as_array[$expected_element]);
                    $this->assertCount($account_type_count, $response_as_array[$expected_element]);
                    break;
                case Account::CREATED_AT:
                case Account::UPDATED_AT:
                    $this->assertDateFormat($response_as_array[$expected_element], DATE_ATOM, $response_as_array[$expected_element]." not in correct format");
                    break;
                case Account::DELETED_AT:
                    if ($response_as_array['active']) {
                        $this->assertNull($response_as_array[$expected_element]);
                    } else {
                        $this->assertDateFormat($response_as_array[$expected_element], DATE_ATOM, $response_as_array[$expected_element]." not in correct format");
                    }
                    break;
                case 'active':
                    $this->assertIsBool($response_as_array[$expected_element]);
                    $this->assertEquals($generated_account->$expected_element, $response_as_array[$expected_element]);
                    break;
                default:
                    $this->assertEquals($generated_account->$expected_element, $response_as_array[$expected_element]);
                    break;
            }
        }
    }

    /**
     * @param array $account_types_in_response
     * @param Collection $generated_account_types
     */
    private function assertAccountTypesOK($account_types_in_response, $generated_account_types) {
        $expected_elements = ['id', 'type', 'name', 'last_digits', 'active'];
        foreach ($account_types_in_response as $account_type_in_response) {
            $this->assertEqualsCanonicalizing($expected_elements, array_keys($account_type_in_response));
            $generated_account_type = $generated_account_types->where('id', $account_type_in_response['id'])->first();
            $this->assertNotEmpty($generated_account_type);
            $this->assertEquals($generated_account_type->toArray(), $account_type_in_response);
        }
    }

}
