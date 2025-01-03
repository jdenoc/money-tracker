<?php

namespace Tests\Feature\Api\Put;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Institution;
use App\Traits\AccountTypeResponseKeys;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PutAccountTypeTest extends TestCase {
    use AccountTypeResponseKeys;

    // uri
    private string $_base_uri = '/api/account-type/%d';

    public function setUp(): void {
        parent::setUp();

        $accounts = Account::factory()
            ->count(3)
            ->for(Institution::factory())
            ->create();
        AccountType::factory()
            ->count(5)
            ->state(new Sequence(function() use ($accounts) {
                return ['account_id' => $accounts->random()->id];
            }))
            ->create();
    }

    public function testUpdateAccountTypeWithoutData() {
        // GIVEN
        $account_type_data = [];
        $account_type = $this->getRandomActiveExistingAccountType();

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $account_type->id), $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public function testUpdateAccountTypeWithInvalidAccountId() {
        // GIVEN
        $existing_account_ids = Account::all()->pluck('id')->toArray();
        $account_type = $this->getRandomActiveExistingAccountType();
        do {
            $account_id = fake()->randomNumber(1);
        } while ($account_id == $account_type->institution_id || in_array($account_id, $existing_account_ids));
        $account_type->account_id = $account_id;

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $account_type->id), $account_type->toArray());

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_ACCOUNT);
    }

    public function testUpdateAccountTypeWithInvalidType() {
        // GIVEN
        $account_type = $this->getRandomActiveExistingAccountType();
        $valid_types = AccountType::getEnumValues();
        do {
            $type = fake()->word();
        } while (in_array($type, $valid_types));
        $account_type->type = $type;

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $account_type->id), $account_type->toArray());

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_TYPE);
    }

    public function testUpdateAccountTypeThatDoesNotExist() {
        // GIVEN
        $existing_account_type_ids = AccountType::all()->pluck('id')->toArray();
        do {
            $account_type_id = fake()->randomNumber(2);
        } while (in_array($account_type_id, $existing_account_type_ids));
        $account_type_data = $this->generateAccountTypeData();
        $account_type_data['account_id'] = $this->getExistingActiveAccountId();

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $account_type_id), $account_type_data->toArray());

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_NOT_FOUND, self::$ERROR_MSG_DOES_NOT_EXIST);
    }

    public static function providerUpdateAccountTypeEachProperty(): array {
        $required_fields = AccountType::getRequiredFieldsForUpdate();

        $test_cases = [];
        foreach ($required_fields as $required_field) {
            $test_cases[$required_field] = [$required_field];
        }
        return $test_cases;
    }

    /**
     * @dataProvider providerUpdateAccountTypeEachProperty
     */
    public function testUpdateAccountTypeEachProperty(string $account_type_data_field) {
        // GIVEN - see providerUpdateAccountEachProperty()
        $account_type = $this->getRandomActiveExistingAccountType();
        $account_type_data = [];
        if ($account_type_data_field == 'account_id') {
            $account_type_data[$account_type_data_field] = $this->getExistingActiveAccountId();
        } else {
            $sample_account_type_data = $this->generateAccountTypeData();
            $account_type_data[$account_type_data_field] = $sample_account_type_data[$account_type_data_field];
        }

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $account_type->id), $account_type_data);

        // THEN
        $failure_message = "PUT Response is " . $response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertEquals($account_type->id, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    public function testUpdateAccountTypeWithoutChangingAnything() {
        // GIVEN
        $account_type = $this->getRandomActiveExistingAccountType();

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $account_type->id), $account_type->toArray());

        // THEN
        $failure_message = "PUT Response is " . $response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertEquals($account_type->id, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function getRandomActiveExistingAccountType() {
        return AccountType::get()->random();
    }

    private function generateAccountTypeData() {
        return AccountType::factory()->make();
    }

    private function getExistingActiveAccountId(): int {
        return Account::all()->random()->id;
    }

    private function assertFailedPostResponse(TestResponse $response, $expected_response_status, $expected_error_message): void {
        $failure_message = "PUT Response is ".$response->getContent();
        $this->assertResponseStatus($response, $expected_response_status, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertFailedPostResponseContent($response_as_array, $expected_error_message, $failure_message);
    }

    private function assertPostResponseHasCorrectKeys(array $response_as_array, string $failure_message): void {
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ERROR, $response_as_array, $failure_message);
    }

    private function assertFailedPostResponseContent(array $response_as_array, string $expected_error_msg, string $failure_message): void {
        $this->assertEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
        $this->assertStringContainsString($expected_error_msg, $response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

}
