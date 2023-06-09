<?php

namespace Tests\Feature\Api\Get;

use App\Models\Institution;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class GetInstitutionsTest extends TestCase {

    protected string $_base_uri = '/api/institutions';

    public function testGetInstitutionsWhenNoneAreAvailable() {
        // GIVEN - no institutions exist in database

        // WHEN
        $response = $this->get($this->_base_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertIsArray($response_body_as_array);
        $this->assertEmpty($response_body_as_array);
    }

    public function providerGetInstitutions(): array {
        return [
            'all active:true'=>[true],      // all institutes are active
            'all active:false'=>[false]     // some institutes are active
        ];
    }

    /**
     * @dataProvider providerGetInstitutions
     * @param bool $all_active
     */
    public function testGetInstitutions(bool $all_active) {
        // GIVEN
        $institutions_count = fake()->randomDigitNotZero();
        $disabled_stamp = function() use ($all_active) { return fake()->boolean(($all_active ? 100 : 50)) ? null : now(); };
        $generated_institutions = Institution::factory()
            ->count($institutions_count)
            ->state([Institution::DELETED_AT=>$disabled_stamp])
            ->create();

        $generated_institutions->makeHidden([Institution::CREATED_AT, Institution::UPDATED_AT, Institution::DELETED_AT]);

        // WHEN
        $response = $this->get($this->_base_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();

        $this->assertNotEmpty($response_body_as_array);
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($institutions_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);
        $this->assertCount($institutions_count, $response_body_as_array);

        $expected_elements = ['id', 'name', 'active'];
        foreach ($response_body_as_array as $institution_in_response) {
            $this->assertEqualsCanonicalizing($expected_elements, array_keys($institution_in_response));

            $generated_institution = $generated_institutions->where('id', $institution_in_response['id'])->first();
            $this->assertNotEmpty($generated_institution);
            $this->assertEquals($generated_institution->toArray(), $institution_in_response);
        }
    }

}
