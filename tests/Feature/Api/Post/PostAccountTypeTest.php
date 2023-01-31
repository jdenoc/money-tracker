<?php

namespace Tests\Feature\Api\Post;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Institution;
use App\Traits\AccountTypeResponseKeys;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PostAccountTypeTest extends TestCase {
    use AccountTypeResponseKeys;
    use WithFaker;

    const METHOD = 'POST';

    private string $_base_uri = '/api/account-type';

    public function setUp(): void {
        parent::setUp();
        Account::factory()
            ->count(3)
            ->state(['disabled'=>false])
            ->for(Institution::factory()->state(['active'=>true]))
            ->create();
    }

    public function testCreateAccountTypeWithoutData() {
        // GIVEN
        $account_type_data = [];

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public function providerCreateAccountMissingProperty(): array {
        // Application must be initialised before factory helpers can be used withi a provider method
        $this->initialiseApplication();
        $account_type_data = $this->generateDummyAccountTypeData();

        $test_cases = [];
        $required_properties = AccountType::getRequiredFieldsForCreation();

        // only 1 property missing
        foreach ($required_properties as $property) {
            $test_cases[$property]['data'] = $account_type_data;
            $test_cases[$property]['error_msg'] = $this->fillMissingPropertyErrorMessage([$property]);
            unset($test_cases[$property]['data'][$property]);
        }

        // 1 < property missing < count(required properties)
        $removed_keys = [];
        $unset_keys = array_rand($required_properties, mt_rand(2, count($required_properties)-1));
        $test_cases['multi-random']['data'] = $account_type_data;
        foreach ($unset_keys as $unset_key) {
            $unset_required_property = $required_properties[$unset_key];
            unset($test_cases['multi-random']['data'][$unset_required_property]);
            $removed_keys[] = $unset_required_property;
        }
        $test_cases['multi-random']['error_msg'] = $this->fillMissingPropertyErrorMessage($removed_keys);

        return $test_cases;
    }

    /**
     * @dataProvider providerCreateAccountMissingProperty
     *
     * @param array  $account_type_data
     * @param string $error_message
     */
    public function testCreateAccountMissingProperty(array $account_type_data, string $error_message) {
        // GIVEN: see providerCreateAccountMissingProperty()
        if (isset($account_type_data['account_id'])) {
            $account_type_data['account_id'] = Account::where('disabled', false)->get()->random()->id;
        }

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, $error_message);
    }

    public function testCreateAccountWithInvalidAccountId() {
        // GIVEN
        $account_type_data = $this->generateDummyAccountTypeData();
        $account_type_data['account_id'] = $this->faker->numberBetween(-999, 0); // account_id should ONLY be an int > 0

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_ACCOUNT);
    }

    public function testCreateAccountWithInvalidType() {
        // GIVEN
        $account_type_data = $this->generateDummyAccountTypeData();
        $account_type_data['account_id'] = Account::where('disabled', false)->get()->random()->id;
        $account_type_data['type'] = $this->faker->word();

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $account_type_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_TYPE);
    }

    public function testCreateAccount() {
        // GIVEN
        $account_type_data = $this->generateDummyAccountTypeData();
        $account_type_data['account_id'] = Account::where('disabled', false)->get()->random()->id;

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $account_type_data);

        // THEN
        $failure_message = self::METHOD." Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_CREATED, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function generateDummyAccountTypeData(): array {
        return AccountType::factory()->make(['disabled'=>false])->toArray();
    }

    private function assertFailedPostResponse(TestResponse $response, $expected_response_status, $expected_error_message) {
        $failure_message = self::METHOD." Response is ".$response->getContent();
        $this->assertResponseStatus($response, $expected_response_status, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertFailedPostResponseContent($response_as_array, $expected_error_message, $failure_message);
    }

    private function assertPostResponseHasCorrectKeys(array $response_as_array, string $failure_message) {
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ID, $response_as_array, $failure_message);
        $this->assertArrayHasKey(self::$RESPONSE_KEY_ERROR, $response_as_array, $failure_message);
    }

    private function assertFailedPostResponseContent(array $response_as_array, string $expected_error_msg, string $failure_message) {
        $this->assertEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertNotEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
        $this->assertStringContainsString($expected_error_msg, $response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

}
