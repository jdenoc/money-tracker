<?php

namespace Tests\Feature\Api\Get;

use App\Account;
use App\Institution;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class GetInstitutionTest extends TestCase {

    use WithFaker;

    /**
     * @var string
     */
    protected $_base_uri = '/api/institution/';

    public function testGetInstitutionWhenNoInstitutionExists(){
        // GIVEN - no institution
        $institution_id = $this->faker->randomDigitNotZero();

        // WHEN
        $response = $this->get($this->_base_uri.$institution_id);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetInstitutionWithoutAccounts(){
        // GIVEN
        $generated_institution = factory(Institution::class)->create();

        // WHEN
        $response = $this->get($this->_base_uri.$generated_institution->id);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertNotEmpty($response_body_as_array);

        $this->assertInstitutionNodesExist($response_body_as_array);
        $this->assertInstitutionNodesValuesExcludingRelationshipsOK($generated_institution, $response_body_as_array);
        $this->assertEmpty($response_body_as_array['accounts']);
    }

    public function testGetInstitution(){
        // GIVEN
        $generated_account_count = $this->faker->randomDigitNotZero();
        $generated_institution = factory(Institution::class)->create();
        $generated_accounts = factory(Account::class, $generated_account_count)->create(['institution_id'=>$generated_institution->id]);
        // These nodes are not in the response output. Lets hide them from the object collection.
        $generated_accounts->makeHidden(['institution_id', 'disabled_stamp']);

        // WHEN
        $response = $this->get($this->_base_uri.$generated_institution->id);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertNotEmpty($response_body_as_array);

        $this->assertInstitutionNodesExist($response_body_as_array);
        $this->assertInstitutionNodesValuesExcludingRelationshipsOK($generated_institution, $response_body_as_array);
        $this->assertNotEmpty($response_body_as_array['accounts']);
        $this->assertCount($generated_account_count, $response_body_as_array['accounts']);
        foreach($response_body_as_array['accounts'] as $institution_account_in_response){
            $this->assertInstitutionAccountNodesOK($institution_account_in_response, $generated_accounts);
        }
    }

    public function assertInstitutionNodesExist($institute_in_response){
        $this->assertArrayHasKey('id', $institute_in_response);
        unset($institute_in_response['id']);
        $this->assertArrayHasKey('name', $institute_in_response);
        unset($institute_in_response['name']);
        $this->assertArrayHasKey('active', $institute_in_response);
        unset($institute_in_response['active']);
        $this->assertArrayHasKey('create_stamp', $institute_in_response);
        unset($institute_in_response['create_stamp']);
        $this->assertArrayHasKey('modified_stamp', $institute_in_response);
        unset($institute_in_response['modified_stamp']);
        $this->assertArrayHasKey('accounts', $institute_in_response);
        unset($institute_in_response['accounts']);
        $this->assertEmpty($institute_in_response);
    }

    public function assertInstitutionNodesValuesExcludingRelationshipsOK($generated_institution, $response_body_as_array){
        $failure_message = 'generated institution:'.json_encode($generated_institution)."\nresponse institution:".json_encode($response_body_as_array);
        $this->assertEquals($generated_institution->id, $response_body_as_array['id'], $failure_message);
        $this->assertEquals($generated_institution->name, $response_body_as_array['name'], $failure_message);
        $this->assertEquals($generated_institution->active, $response_body_as_array['active'], $failure_message);
        $this->assertDateFormat($response_body_as_array['create_stamp'], Carbon::ATOM, $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertDateFormat($response_body_as_array['modified_stamp'], Carbon::ATOM, $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
        $this->assertTrue(is_array($response_body_as_array['accounts']), $failure_message);
    }

    public function assertInstitutionAccountNodesOK($institution_account_in_response, $generated_accounts){
        $generated_accounts_as_array = $generated_accounts->toArray();
        $error_msg = "account in response:".json_encode($institution_account_in_response)."\ngenerated accounts:".json_encode($generated_accounts_as_array);

        $this->assertArrayHasKey('id', $institution_account_in_response, $error_msg);
        $this->assertArrayHasKey('name', $institution_account_in_response, $error_msg);
        $this->assertArrayHasKey('disabled', $institution_account_in_response, $error_msg);
        $this->assertArrayHasKey('total', $institution_account_in_response, $error_msg);
        $this->assertTrue(in_array($institution_account_in_response, $generated_accounts_as_array), $error_msg);
    }

}