<?php

namespace Tests\Unit;

use App\AccountType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Account;

class AccountTest extends TestCase {

    use DatabaseMigrations;

    public function testFindAccountWithTypes(){
        $generated_account = factory(Account::class)->create();
        factory(AccountType::class, 2)->create(['account_group'=>$generated_account->id]);

        $account = Account::find_account_with_types($generated_account->id);
        $this->assertNotEmpty($account);

        // account details were correct, excluding account_types
        $generated_account_as_array = $generated_account->toArray();
        $account_as_array = $account->toArray();
        unset($account_as_array['account_types']);
        $this->assertEquals($generated_account_as_array, $account_as_array);

//        foreach($generated_account->account_types as $account_type){
//            $this->assertContains($account_types, $account->account_types());
            //        'id'
            //        'type'
            //        'type_name'
            //        'last_digits'
            //        'last_updated'
//        }

        $this->markTestIncomplete();
    }

}