<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Account;

class AccountsTest extends TestCase {

    use DatabaseMigrations;

    /**
     * tests GET /api/accounts
     */
    public function testGetAccounts(){
        $account_count=2;
        $accounts = factory(Account::class, $account_count)->create();

        $response = $this->get('/api/accounts');
        $response->assertStatus(200);
        $response->assertJson(['count'=>$account_count]);
        $response->assertJson($accounts->toArray());
    }

}