<?php

namespace Tests\Feature\Api\Put;

use App\Models\Account;
use App\Models\Institution;
use App\Traits\AccountResponseKeys;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PutAccountTest extends TestCase {
    use AccountResponseKeys;
    use WithFaker;

    const METHOD = "PUT";

    private string $_base_uri = '/api/account/%d';

    public function setUp(): void {
        parent::setUp();

        Account::factory()
            ->count(10)
            ->for(Institution::factory())
            ->create();
    }

    public function testUpdateAccountWithoutData() {
        // GIVEN
        $account_data = [];
        $account = $this->getRandomActiveExistingAccount();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $account->id), $account_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public function testUpdateAccountWithInvalidInstitutionId() {
        // GIVEN
        $existing_instituion_ids = Institution::all()->pluck('id')->toArray();
        $account = $this->getRandomActiveExistingAccount();
        do {
            // there should only be 1 institution in existance
            $institution_id = $this->faker->randomNumber(1);
        } while ($institution_id == $account->institution_id || in_array($institution_id, $existing_instituion_ids));
        $account->institution_id = $institution_id;

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $account->id), $account->toArray());

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_INSTITUTION);
    }

    public function testUpdateAccountWithInvalidCurrencyCode() {
        // GIVEN
        $account = $this->getRandomActiveExistingAccount();
        $account_data = $account->toArray();
        $account_data['currency'] = 'XXX'; // XXX is an invalid currency code and not listed in the ISO 4217 standard

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $account->id), $account_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_INVALID_CURRENCY);
    }

    public function testUpdateAccountThatDoesNotExist() {
        // GIVEN
        $existing_account_ids = Account::all()->pluck('id')->toArray();
        do {
            $account_id = $this->faker->randomNumber(2);
        } while (in_array($account_id, $existing_account_ids));
        $account_data = $this->generateAccountData();
        $account_data['institution_id'] = $this->getExistingActiveInstitutionId();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $account_id), $account_data->toArray());

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_NOT_FOUND, self::$ERROR_MSG_DOES_NOT_EXIST);
    }

    public function providerUpdateAccountEachProperty(): array {
        $this->initialiseApplication();
        $dummy_account_data = $this->generateAccountData();
        $required_fields = Account::getRequiredFieldsForUpdate();

        $test_cases = [];
        foreach ($required_fields as $required_field) {
            $test_cases[$required_field]['data'] = [$required_field=>$dummy_account_data->{$required_field}];
        }
        return $test_cases;
    }

    /**
     * @dataProvider providerUpdateAccountEachProperty
     * @param array $account_data
     */
    public function testUpdateAccountEachProperty(array $account_data) {
        // GIVEN - see providerUpdateAccountEachProperty()
        $account = $this->getRandomActiveExistingAccount();
        if (isset($account_data['institution_id'])) {
            $account_data['institution_id'] = $this->getExistingActiveInstitutionId();
        }

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $account->id), $account_data);

        // THEN
        $failure_message = self::METHOD." Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertEquals($account->id, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    public function testUpdateAccountWithoutChangingAnything() {
        // GIVEN
        $account = $this->getRandomActiveExistingAccount();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $account->id), $account->toArray());

        // THEN
        $failure_message = self::METHOD." Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertEquals($account->id, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function getRandomActiveExistingAccount() {
        return Account::all()->random();
    }

    private function generateAccountData() {
        return Account::factory()->make();
    }

    private function getExistingActiveInstitutionId(): int {
        return Institution::all()->random()->id;
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
