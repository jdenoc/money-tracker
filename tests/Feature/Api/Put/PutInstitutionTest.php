<?php

namespace Tests\Feature\Api\Put;

use App\Models\Institution;
use App\Traits\InstitutionResponseKeys;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PutInstitutionTest extends TestCase {
    use InstitutionResponseKeys;
    use WithFaker;

    const METHOD = 'PUT';

    private string $_base_uri = '/api/institution/%d';

    public function setUp(): void {
        parent::setUp();
        Institution::factory()->count(3)->create();
    }

    public function testUpdateInstitutionWithoutData() {
        // GIVEN
        $institution_data = [];
        $institution = $this->getRandomActiveExistingInstitution();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $institution->id), $institution_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public function testUpdateInstitutionThatDoesNotExist() {
        // GIVEN
        $existing_ids = Institution::all()->pluck('id')->toArray();
        do {
            $institution_id = $this->faker->randomNumber(2);
        } while (in_array($institution_id, $existing_ids));
        $institution_data = $this->generateInstitutionData();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $institution_id), $institution_data->toArray());

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_NOT_FOUND, self::$ERROR_MSG_DOES_NOT_EXIST);
    }

    public function providerUpdateInstitutionEachProperty(): array {
        $this->initialiseApplication();
        $dummy_account_data = $this->generateInstitutionData();
        $required_fields = Institution::getRequiredFieldsForUpdate();

        $test_cases = [];
        foreach ($required_fields as $required_field) {
            $test_cases[$required_field]['data'] = [$required_field=>$dummy_account_data->{$required_field}];
        }
        return $test_cases;
    }

    /**
     * @dataProvider providerUpdateInstitutionEachProperty
     * @param array $institution_data
     */
    public function testUpdateInstitutionEachProperty(array $institution_data) {
        // GIVEN - see providerUpdateInstitutionEachProperty()
        $institution = $this->getRandomActiveExistingInstitution();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $institution->id), $institution_data);

        // THEN
        $failure_message = self::METHOD." Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertEquals($institution->id, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    public function testUpdateInstitutionWithoutChangingAnything() {
        // GIVEN
        $institution = $this->getRandomActiveExistingInstitution();

        // WHEN
        $response = $this->json(self::METHOD, sprintf($this->_base_uri, $institution->id), $institution->toArray());

        // THEN
        $failure_message = self::METHOD." Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertEquals($institution->id, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function getRandomActiveExistingInstitution() {
        return Institution::all()->random();
    }

    private function generateInstitutionData() {
        return Institution::factory()->make();
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
