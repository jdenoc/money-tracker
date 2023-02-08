<?php

namespace Tests\Feature\Api\Post;

use App\Models\Institution;
use App\Traits\InstitutionResponseKeys;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PostInstitutionTest extends TestCase {
    use InstitutionResponseKeys;

    const METHOD = "POST";

    private string $_base_uri = '/api/institution';

    public function testCreateInstitutionWithoutData() {
        // GIVEN
        $institution_data = [];

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $institution_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, self::$ERROR_MSG_NO_DATA);
    }

    public function providerCreateInstitutionWithMissingProperty(): array {
        // Application must be initialised before factory helpers can be used with a provider method
        $this->initialiseApplication();
        $institution_data = $this->generateDummyInstitutionData();

        $test_cases = [];
        $required_properties = Institution::getRequiredFieldsForCreation();

        // only 1 property missing
        foreach ($required_properties as $property) {
            $test_cases[$property]['data'] = $institution_data;
            $test_cases[$property]['error_msg'] = $this->fillMissingPropertyErrorMessage([$property]);
            unset($test_cases[$property]['data'][$property]);
        }

        return $test_cases;
    }

    /**
     * @dataProvider providerCreateInstitutionWithMissingProperty
     *
     * @param array  $institution_data
     * @param string $error_message
     */
    public function testCreateInstitutionWithMissingProperty(array $institution_data, string $error_message) {
        // GIVEN: see providerCreateInstitutionWithMissingProperty()
        if (empty($institution_data)) {
            $this->markTestSkipped('Institution data not provided. Data required to create an institution:'.print_r(Institution::getRequiredFieldsForCreation(), true));
        }

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $institution_data);

        // THEN
        $this->assertFailedPostResponse($response, HttpStatus::HTTP_BAD_REQUEST, $error_message);
    }

    public function testCreateInstitution() {
        // GIVEN
        $institution_data = $this->generateDummyInstitutionData();

        // WHEN
        $response = $this->json(self::METHOD, $this->_base_uri, $institution_data);

        // THEN
        $failure_message = self::METHOD." Response is ".$response->getContent();
        $this->assertResponseStatus($response, HttpStatus::HTTP_CREATED, $failure_message);
        $response_as_array = $response->json();
        $this->assertPostResponseHasCorrectKeys($response_as_array, $failure_message);
        $this->assertNotEquals(self::$ERROR_ID, $response_as_array[self::$RESPONSE_KEY_ID], $failure_message);
        $this->assertEmpty($response_as_array[self::$RESPONSE_KEY_ERROR], $failure_message);
    }

    private function assertFailedPostResponse(TestResponse $response, $expected_response_status, string $expected_error_message) {
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

    private function generateDummyInstitutionData(): array {
        $institution_data = Institution::factory()->make();
        return [
            'name'=>$institution_data->name,
        ];
    }

}
