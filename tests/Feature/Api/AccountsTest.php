<?php

namespace Tests\Feature\Api;

use App\AccountType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Account;

class AccountsTest extends TestCase {

    use DatabaseMigrations;

    /**
     * SCENARIO: obtain a list of accounts
     *  GIVEN:   there are accounts in the database
     *  WHEN:    visiting GET /api/accounts
     *  THEN:    receive a 200 status
     *  AND:     get a list of accounts in json format
     *           example output: { {"id": 1, "account": "account1", "total": "0.00"}, {"id": 2, "account": "account2", "total": "0.00"}, "count": 2 }
     */
    public function testGetListOfAccountsWhenTheyAreAvailable(){
        // GIVEN
        $account_count = 2;
        $generated_accounts = factory(Account::class, $account_count)->create();

        // WHEN
        $response = $this->get('/api/accounts');

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
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
                "Factory generate account in JSON: ".$generated_account->toJson()."\nResponse Body:".$response_body
            );
        }
    }

    /**
     * SCENARIO: obtain a list of accounts
     *  GIVEN:   there are NO accounts in the database
     *  WHEN:    visiting GET /api/accounts
     *  THEN:    receive a 404 status
     *  AND:     get an empty array in json format
     *           example output: []
     */
    public function testGetListOfAccountsWhenNoAccountsAreAvailable(){
        // GIVEN - nothing. there should be no data in database

        // WHEN
        $response = $this->get("/api/accounts");

        // THEN
        $response->assertStatus(404);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

}