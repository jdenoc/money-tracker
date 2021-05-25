<?php

namespace Tests\Feature\Api\Get;

use App\Institution;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class GetInstitutionsTest extends TestCase {

    use WithFaker;

    protected $_base_uri = '/api/institutions';

    public function testGetInstitutionsWhenNoneAreAvailable(){
        // GIVEN - no institutions exist in database

        // WHEN
        $response = $this->get($this->_base_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    public function providerGetInstitutions(){
        return [
            'all active:true'=>[true],      // all institutes are active
            'all active:false'=>[false]     // some institutes are active
        ];
    }

    /**
     * @dataProvider providerGetInstitutions
     * @param bool $all_active
     */
    public function testGetInstitutions(bool $all_active){
        // GIVEN
        $institutions_count = $this->faker->randomDigitNotNull;
        $generated_institutions = [];
        for($i=0; $i<$institutions_count; $i++){
            $active_flag = $this->faker->boolean(($all_active? 100:50));
            $generated_institution = factory(Institution::class)->create(['active'=>$active_flag]);
            $generated_institutions[$generated_institution->id] = $generated_institution;
            unset($generated_institution, $active_flag);
        }

        // WHEN
        $response = $this->get($this->_base_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertNotEmpty($response_body_as_array);
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($institutions_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);
        $this->assertCount($institutions_count, $response_body_as_array);
        foreach($response_body_as_array as $institution_in_response){
            $this->assertArrayHasKey('id', $institution_in_response);
            $this->assertArrayHasKey('name', $institution_in_response);
            $this->assertArrayHasKey('active', $institution_in_response);

            $this->assertNotEmpty($generated_institutions[$institution_in_response['id']]);
            $generated_institution = $generated_institutions[$institution_in_response['id']];
            // factory doesn't set timestamps, so we don't need to make sure they are NOT present
            $this->assertEquals($generated_institution->toArray(), $institution_in_response);
            unset(
                $institution_in_response['id'],
                $institution_in_response['name'],
                $institution_in_response['active']
            );
            $this->assertEmpty($institution_in_response);
        }
    }

}