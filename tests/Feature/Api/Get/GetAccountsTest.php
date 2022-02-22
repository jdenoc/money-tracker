<?php

namespace Tests\Feature\Api\Get;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

use App\Account;

class GetAccountsTest extends TestCase {

    use WithFaker;

    private $_uri = '/api/accounts';

    public function testGetListOfAccountsWhenTheyAreAvailable(){
        // GIVEN
        $account_count = $this->faker->randomDigitNotZero();
        $generated_accounts = factory(Account::class, $account_count)->create();
        // These nodes are not in the response output. Lets hide them from the object collection.
        $generated_accounts->makeHidden(['disabled_stamp']);

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($account_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);
        foreach($response_body_as_array as $account_in_response){
            $this->assertArrayHasKey('id', $account_in_response);
            unset($account_in_response['id']);
            $this->assertArrayHasKey('name', $account_in_response);
            unset($account_in_response['name']);
            $this->assertArrayHasKey('institution_id', $account_in_response);
            unset($account_in_response['institution_id']);
            $this->assertArrayHasKey('disabled', $account_in_response);
            unset($account_in_response['disabled']);
            $this->assertArrayHasKey('total', $account_in_response);
            unset($account_in_response['total']);
            $this->assertArrayHasKey('currency', $account_in_response);
            unset($account_in_response['currency']);
            $this->assertEmpty($account_in_response, "Unknown nodes found in JSON response:".json_encode($account_in_response));
        }
        foreach($generated_accounts as $generated_account){
            $this->assertTrue(
                in_array($generated_account->toArray(), $response_body_as_array),
                "Factory generate account in JSON: ".$generated_account->toJson()."\nResponse Body:".$response->getContent()
            );
        }
    }

    public function testGetListOfAccountsWhenNoAccountsAreAvailable(){
        // GIVEN - nothing. there should be no data in database

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

}