<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;

use App\Account;

class GetAccountsTest extends TestCase {

    use DatabaseMigrations;

    private $_uri = '/api/accounts';

    public function testGetListOfAccountsWhenTheyAreAvailable(){
        // GIVEN
        $account_count = 2;
        $generated_accounts = factory(Account::class, $account_count)->create();

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $this->getResponseAsArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($account_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);
        foreach($response_body_as_array as $account_in_response){
            $this->assertArrayHasKey('id', $account_in_response);
            $this->assertArrayHasKey('account', $account_in_response);
            $this->assertArrayHasKey('total', $account_in_response);
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
        $response_body_as_array = $this->getResponseAsArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

}