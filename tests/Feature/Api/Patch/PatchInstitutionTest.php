<?php

namespace Tests\Feature\Api\Patch;

use App\Models\Institution;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class PatchInstitutionTest extends TestCase {
    use WithFaker;

    private const PLACEHOLDER_URI_INSTITUTION = '/api/institution/%d';

    public function testRestoreInstitutionThatDoesNotExist() {
        // GIVEN
        $institution_id = $this->faker->randomNumber();
        // configm institution does not exist
        $dummy_institution = Institution::find($institution_id);
        $this->assertNull($dummy_institution);

        // WHEN
        $response = $this->patch(sprintf(self::PLACEHOLDER_URI_INSTITUTION, $institution_id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testRestoreInstitutionThatIsAlreadyActive() {
        // GIVEN
        $institution = Institution::factory()->create([Institution::DELETED_AT=>now()]);

        // WHEN
        $response = $this->patch(sprintf(self::PLACEHOLDER_URI_INSTITUTION, $institution->id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testRestoreInstitution() {
        // GIVEN
        $institution = Institution::factory()->create([Institution::DELETED_AT=>null]);

        // WHEN
        $response = $this->patch(sprintf(self::PLACEHOLDER_URI_INSTITUTION, $institution->id));

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getContent());
    }

}
