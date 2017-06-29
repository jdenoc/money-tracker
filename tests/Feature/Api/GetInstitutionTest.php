<?php

namespace Tests\Feature\Api;

use App\Account;
use App\Institution;
use Carbon\Carbon;
use Faker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GetInstitutionTest extends TestCase {

    use DatabaseMigrations;

    /**
     * @var string
     */
    protected $_base_uri = '/api/institution/';

    /**
     * @var Faker\Generator
     */
    protected $_faker;

    public function setUp(){
        parent::setUp();
        $this->_faker = Faker\Factory::create();
    }

    public function testGetInstitutionWhenNoInstitutionExists(){
        // GIVEN - no institution
        $institution_id = $this->_faker->randomDigitNotNull;

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
        $generated_account_count = $this->_faker->randomDigitNotNull;
        $generated_institution = factory(Institution::class)->create();
        $generated_accounts = factory(Account::class, $generated_account_count)->create(['institution_id'=>$generated_institution->id]);

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

    public function assertInstitutionNodesExist($response_body_as_array){
        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertArrayHasKey('name', $response_body_as_array);
        $this->assertArrayHasKey('active', $response_body_as_array);
        $this->assertArrayHasKey('create_stamp', $response_body_as_array);
        $this->assertArrayHasKey('modified_stamp', $response_body_as_array);
        $this->assertArrayHasKey('accounts', $response_body_as_array);
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
        $account_array_count = 0;
        $generated_accounts_as_array = [];
        foreach($generated_accounts as $generated_account){
            $generated_accounts_as_array[$account_array_count]['id'] = $generated_account->id;
            $generated_accounts_as_array[$account_array_count]['account'] = $generated_account->account;
            $generated_accounts_as_array[$account_array_count]['total'] = $generated_account->total;
            $account_array_count++;
        }

        $this->assertArrayHasKey('id', $institution_account_in_response);
        $this->assertArrayHasKey('account', $institution_account_in_response);
        $this->assertArrayHasKey('total', $institution_account_in_response);
        $this->assertTrue(
            in_array($institution_account_in_response, $generated_accounts_as_array),
            "account in response:".json_encode($institution_account_in_response)."\ngenerated accounts:".json_encode($generated_accounts_as_array)
        );
    }

}