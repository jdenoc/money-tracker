<?php

namespace Tests\Feature\Api\Put;

use App\Models\Institution;
use App\Traits\InstitutionResponseKeys;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PutInstitutionTest extends TestCase {
    use InstitutionResponseKeys;

    // uri
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
        $response = $this->putJson(sprintf($this->_base_uri, $institution->id), $institution_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public function testUpdateInstitutionThatDoesNotExist() {
        // GIVEN
        $existing_ids = Institution::all()->pluck('id')->toArray();
        do {
            $institution_id = fake()->randomNumber(2);
        } while (in_array($institution_id, $existing_ids));
        $institution_data = self::generateInstitutionData();

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $institution_id), $institution_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_NOT_FOUND, self::$ERROR_MSG_DOES_NOT_EXIST);
    }

    public static function providerUpdateInstitutionEachProperty(): array {
        $required_fields = Institution::getRequiredFieldsForUpdate();

        $test_cases = [];
        foreach ($required_fields as $required_field) {
            $test_cases[$required_field] = ['institution_data_field' => $required_field];
        }
        return $test_cases;
    }

    /**
     * @dataProvider providerUpdateInstitutionEachProperty
     */
    public function testUpdateInstitutionEachProperty(string $institution_data_field) {
        // GIVEN - see providerUpdateInstitutionEachProperty()
        $institution = $this->getRandomActiveExistingInstitution();
        $sample_institution_data = $this->generateInstitutionData();
        $institution_data = [$institution_data_field => $sample_institution_data[$institution_data_field]];

        // WHEN
        $response = $this->putJson(sprintf($this->_base_uri, $institution->id), $institution_data);

        // THEN
        $failure_message = "PUT Response is ".$response->getContent();
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
        $response = $this->putJson(sprintf($this->_base_uri, $institution->id), $institution->toArray());

        // THEN
        $failure_message = "PUT Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertEquals($institution->id, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function getRandomActiveExistingInstitution() {
        return Institution::all()->random();
    }

    private function generateInstitutionData(): array {
        $dummy_institution = Institution::factory()->make();
        return [
            'name' => $dummy_institution->name,
        ];
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
