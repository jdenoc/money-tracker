<?php

namespace Tests\Feature\Api\Delete;

use App\Models\Institution;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class DeleteInstitutionTest extends TestCase {

    // uri
    private const PLACEHOLDER_URI_INSTITUTION = '/api/institution/%d';

    public function testDisableInstitutionThatDoesNotExist() {
        // GIVEN
        $institution_id = fake()->randomNumber();
        // confirm account does not exist
        $dummy_institution = Institution::find($institution_id);
        $this->assertNull($dummy_institution);

        // WHEN
        $response = $this->delete(sprintf(self::PLACEHOLDER_URI_INSTITUTION, $institution_id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testDisableInstitutionThatIsAlreadyDisabled() {
        // GIVEN
        $institution = Institution::factory()->disabled()->create();

        // WHEN
        $response = $this->delete(sprintf(self::PLACEHOLDER_URI_INSTITUTION, $institution->id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testDisableInstitution() {
        // GIVEN
        $institution = Institution::factory()->create();

        // WHEN
        $response = $this->delete(sprintf(self::PLACEHOLDER_URI_INSTITUTION, $institution->id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getContent());
    }

}
