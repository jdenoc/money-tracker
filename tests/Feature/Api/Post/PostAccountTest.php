<?php

namespace Tests\Feature\Api\Post;

use App\Models\Account;
use App\Models\Institution;
use App\Traits\AccountResponseKeys;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PostAccountTest extends TestCase {
    use AccountResponseKeys;

    private string $_base_uri = '/api/account';

    public function setUp(): void {
        parent::setUp();
        Institution::factory()->count(3)->create();
    }

    public function testCreateAccountWithoutData() {
        // GIVEN
        $account_data = [];

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public static function providerCreateAccountMissingProperty(): array {
        $test_cases = [];
        $required_properties = Account::getRequiredFieldsForCreation();

        // only 1 property missing
        foreach ($required_properties as $property) {
            $test_cases[$property] = ['missing_fields'=>[$property],];
        }

        // 1 < property missing < count(required properties)
        $missing_fields = array_rand(array_flip($required_properties), mt_rand(2, count($required_properties)-1));
        $test_cases['multi-random'] = ['missing_fields'=>$missing_fields];

        return $test_cases;
    }

    /**
     * @dataProvider providerCreateAccountMissingProperty
     */
    public function testCreateAccountMissingProperty(array $missing_fields) {
        // GIVEN
        $account_data = $this->generateDummyAccountData();
        if (!in_array('institution_id', $missing_fields)) {
            $account_data = $this->setValidInstitutionId($account_data);
        }
        $account_data = array_diff_key($account_data, array_flip($missing_fields));

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, $this->fillMissingPropertyErrorMessage($missing_fields));
    }

    public function testCreateAccountWithInvalidInstitutionId() {
        // GIVEN
        $account_data = $this->generateDummyAccountData();
        $account_data['institution_id'] = fake()->numberBetween(-999, 0); // institution_id should ONLY be an int > 0

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_INSTITUTION);
    }

    public function testCreateAccountWithInvalidCurrencyCode() {
        // GIVEN
        $account_data = $this->generateDummyAccountData();
        $account_data = $this->setValidInstitutionId($account_data);
        $account_data['currency'] = 'XXX'; // XXX is an invalid currency code and not listed in the ISO 4217 standard

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_CURRENCY);
    }

    public function testCreateAccount() {
        // GIVEN
        $account_data = $this->generateDummyAccountData();
        $account_data = $this->setValidInstitutionId($account_data);

        // WHEN
        $response = $this->postJson($this->_base_uri, $account_data);

        // THEN
        $failure_message = "POST Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_CREATED, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function generateDummyAccountData(): array {
        $account_data = Account::factory()->make();
        return [
            'name'=>$account_data->name,
            'institution_id'=>$account_data->institution_id,
            'total'=>$account_data->total,
            'currency'=>$account_data->currency
        ];
    }

    private function setValidInstitutionId(array $account_data): array {
        $institution_id = Institution::get()->random()->id;
        $account_data['institution_id'] = $institution_id;
        return $account_data;
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
