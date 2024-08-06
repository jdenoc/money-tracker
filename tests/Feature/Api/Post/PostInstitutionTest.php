<?php

namespace Tests\Feature\Api\Post;

use App\Models\Institution;
use App\Traits\InstitutionResponseKeys;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PostInstitutionTest extends TestCase {
    use InstitutionResponseKeys;

    private string $_base_uri = '/api/institution';

    public function testCreateInstitutionWithoutData() {
        // GIVEN
        $institution_data = [];

        // WHEN
        $response = $this->postJson($this->_base_uri, $institution_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public static function providerCreateInstitutionWithMissingProperty(): array {
        $test_cases = [];
        $required_properties = Institution::getRequiredFieldsForCreation();

        // only 1 property missing
        foreach ($required_properties as $property) {
            $test_cases[$property] = ['missing_property'=>$property];
        }

        return $test_cases;
    }

    /**
     * @dataProvider providerCreateInstitutionWithMissingProperty
     */
    public function testCreateInstitutionWithMissingProperty(string $missing_property) {
        // GIVEN
        $institution_data = $this->generateDummyInstitutionData();
        unset($institution_data[$missing_property]);
        if (empty($institution_data)) {
            $this->markTestSkipped('Institution data not provided. Data required to create an institution:'.print_r(Institution::getRequiredFieldsForCreation(), true));
        }

        // WHEN
        $response = $this->postJson($this->_base_uri, $institution_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, $this->fillMissingPropertyErrorMessage([$missing_property]));
    }

    public function testCreateInstitution() {
        // GIVEN
        $institution_data = $this->generateDummyInstitutionData();

        // WHEN
        $response = $this->postJson($this->_base_uri, $institution_data);

        // THEN
        $failure_message = "POST Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_CREATED, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function assertFailedPostResponse(TestResponse $response, $expected_response_status, string $expected_error_message): void {
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

    private function generateDummyInstitutionData(): array {
        $institution_data = Institution::factory()->make();
        return [
            'name'=>$institution_data->name,
        ];
    }

}
