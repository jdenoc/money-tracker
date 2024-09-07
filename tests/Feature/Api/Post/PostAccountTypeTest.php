<?php

namespace Tests\Feature\Api\Post;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Institution;
use App\Traits\AccountTypeResponseKeys;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PostAccountTypeTest extends TestCase {
    use AccountTypeResponseKeys;

    // uri
    private string $_base_uri = '/api/account-type';

    public function setUp(): void {
        parent::setUp();
        Account::factory()
            ->count(3)
            ->for(Institution::factory())
            ->create();
    }

    public function testCreateAccountTypeWithoutData() {
        // GIVEN
        $account_type_data = [];

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public static function providerCreateAccountMissingProperty(): array {
        $test_cases = [];
        $required_properties = AccountType::getRequiredFieldsForCreation();

        // only 1 property missing
        foreach ($required_properties as $property) {
            $test_cases[$property] = ['missing_properties' => [$property]];
        }

        // 1 < property missing < count(required properties)
        $missing_properties = array_rand(array_flip($required_properties), mt_rand(2, count($required_properties) - 1));
        $test_cases['multi-random'] = ['missing_properties' => $missing_properties];

        return $test_cases;
    }

    /**
     * @dataProvider providerCreateAccountMissingProperty
     */
    public function testCreateAccountMissingProperty(array $missing_properties) {
        // GIVEN
        $account_type_data = $this->generateDummyAccountTypeData();
        if(!in_array('account_id', $missing_properties)) {
            // set a valid account ID
            $account_type_data['account_id'] = Account::all()->random()->id;
        }
        $account_type_data = array_diff_key($account_type_data, array_flip($missing_properties));

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, $this->fillMissingPropertyErrorMessage($missing_properties));
    }

    public function testCreateAccountWithInvalidAccountId() {
        // GIVEN
        $account_type_data = $this->generateDummyAccountTypeData();
        $account_type_data['account_id'] = fake()->numberBetween(-999, 0); // account_id should ONLY be an int > 0

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_ACCOUNT);
    }

    public function testCreateAccountWithInvalidType() {
        // GIVEN
        $account_type_data = $this->generateDummyAccountTypeData();
        $account_type_data['account_id'] = Account::all()->random()->id;
        $account_type_data['type'] = fake()->word();

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_TYPE);
    }

    public function testCreateAccount() {
        // GIVEN
        $account_type_data = $this->generateDummyAccountTypeData();
        $account_type_data['account_id'] = Account::all()->random()->id;

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_type_data);

        // THEN
        $failure_message = "POST Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_CREATED, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function generateDummyAccountTypeData(): array {
        return AccountType::factory()->make()->toArray();
    }

    private function assertFailedPostResponse(TestResponse $response, $expected_response_status, $expected_error_message): void {
        $failure_message = "POST Response is ".$response->getContent();
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
