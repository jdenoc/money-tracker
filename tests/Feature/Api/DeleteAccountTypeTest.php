<?php

namespace Tests\Feature\Api;

use App\Account;
use App\AccountType;
use Faker\Factory as FakerFactory;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DeleteAccountTypeTest extends TestCase {

    use DatabaseMigrations;

    private $_disable_account_type_uri = '/api/account-type/';
    private $_get_account_uri = '/api/account/';

    public function testDisableAccountTypeThatDoesNotExist(){
        $faker = FakerFactory::create();
        // GIVEN - account_type does not exist
        $account_type_id = $faker->randomNumber();

        // WHEN
        $response = $this->delete($this->_disable_account_type_uri.$account_type_id);

        // THEN
        // confirm there are no database records
        $account_type_collection = AccountType::all();
        $this->assertTrue($account_type_collection->isEmpty(), $account_type_collection->toJson());

        // confirm we got the right response
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($response->getContent());
    }

    public function testDisabledAccountType(){
        // GIVEN
        $generated_account = factory(Account::class)->create();
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$generated_account->id]);

        // WHEN
        $account_response1 = $this->get($this->_get_account_uri.$generated_account->id);    // make this call to confirm account type is NOT disabled
        $disabled_response = $this->delete($this->_disable_account_type_uri.$generated_account_type->id);
        $account_response2 = $this->get($this->_get_account_uri.$generated_account->id);    // make this call to confirm account type is disabled

        // THEN
        $this->assertResponseStatus($account_response1, HttpStatus::HTTP_OK);
        $this->assertResponseStatus($disabled_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertResponseStatus($account_response2, HttpStatus::HTTP_OK);

        $account_response1_as_array = $account_response1->json();
        $this->assertNotEmpty($account_response1_as_array, $account_response1->getContent());
        $this->assertArrayHasKey('account_types', $account_response1_as_array, $account_response1->getContent());
        $this->assertTrue(is_array($account_response1_as_array['account_types']), $account_response1->getContent());
        $this->assertNotEmpty($account_response1_as_array['account_types'], $account_response1->getContent());
        $this->assertCount(1, $account_response1_as_array['account_types'], "We only created 1 account_type, why has this happened\n".$account_response1->getContent());
        foreach($account_response1_as_array['account_types'] as $account_type_in_response){
            $this->assertTrue(is_array($account_type_in_response), $account_response1->getContent());
            $this->assertArrayHasKey('disabled', $account_type_in_response, $account_response1->getContent());
            $this->assertFalse($account_type_in_response['disabled'], $account_response1->getContent());
        }

        $this->assertEmpty($disabled_response->getContent());

        $account_response2_as_array = $account_response2->json();
        $this->assertNotEmpty($account_response2_as_array, $account_response2->getContent());
        $this->assertArrayHasKey('account_types', $account_response2_as_array, $account_response2->getContent());
        $this->assertTrue(is_array($account_response2_as_array['account_types']), $account_response2->getContent());
        $this->assertNotEmpty($account_response2_as_array['account_types'], $account_response2->getContent());
        $this->assertCount(1, $account_response2_as_array['account_types'], "We only created 1 account_type, why has this happened\n".$account_response2->getContent());
        foreach($account_response2_as_array['account_types'] as $account_type_in_response){
            $this->assertTrue(is_array($account_type_in_response), $account_response2->getContent());
            $this->assertArrayHasKey('disabled', $account_type_in_response, $account_response2->getContent());
            $this->assertTrue($account_type_in_response['disabled'], $account_response2->getContent());
        }
    }

}