<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Account;
use App\AccountType;

class AccountTest extends TestCase {

    use DatabaseMigrations;

    public function testFindAccountWithTypes(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id]);

        // WHEN
        $account = Account::find_account_with_types($generated_account->id);

        // THEN
        $this->assertNotEmpty($account);
        $account_as_array = $account->toArray();
        $this->assertArrayHasKey('id', $account_as_array);
        $this->assertArrayHasKey('account', $account_as_array);
        $this->assertArrayHasKey('total', $account_as_array);
        $this->assertArrayHasKey('account_types', $account_as_array);
        $this->assertTrue(is_array($account_as_array['account_types']));
        $this->assertEquals($account_type_count, count($account_as_array['account_types']));
        foreach($account_as_array['account_types'] as $account_type){
            $this->assertArrayHasKey('id', $account_type);
            $this->assertArrayHasKey('type', $account_type);
            $this->assertArrayHasKey('type_name', $account_type);
            $this->assertArrayHasKey('account_group', $account_type);
            $this->assertArrayHasKey('disabled', $account_type);
            $this->assertArrayHasKey('last_digits', $account_type);
            $this->assertArrayHasKey('last_updated', $account_type);
        }
    }

    public function testFindAccountWithTypesAndOneIsDisabled(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id]);
        factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id, 'disabled'=>1]);

        // WHEN
        $account = Account::find_account_with_types($generated_account->id);

        // THEN
        $this->assertNotEmpty($account);
        $account_as_array = $account->toArray();
        $this->assertArrayHasKey('id', $account_as_array);
        $this->assertArrayHasKey('account', $account_as_array);
        $this->assertArrayHasKey('total', $account_as_array);
        $this->assertArrayHasKey('account_types', $account_as_array);
        $this->assertTrue(is_array($account_as_array['account_types']));
        $this->assertEquals($account_type_count, count($account_as_array['account_types']));
        foreach($account_as_array['account_types'] as $account_type){
            $this->assertArrayHasKey('id', $account_type);
            $this->assertArrayHasKey('type', $account_type);
            $this->assertArrayHasKey('type_name', $account_type);
            $this->assertArrayHasKey('account_group', $account_type);
            $this->assertArrayHasKey('disabled', $account_type);
            $this->assertArrayHasKey('last_digits', $account_type);
            $this->assertArrayHasKey('last_updated', $account_type);
        }
    }

    public function testFindAccountWithoutTypes(){
        // GIVEN
        $generated_account = factory(Account::class)->create();

        // WHEN
        $account = Account::find_account_with_types($generated_account->id);

        // THEN
        $this->assertNotEmpty($account);
        $account_as_array = $account->toArray();
        $this->assertArrayHasKey('id', $account_as_array);
        $this->assertArrayHasKey('account', $account_as_array);
        $this->assertArrayHasKey('total', $account_as_array);
        $this->assertArrayHasKey('account_types', $account_as_array);
        $this->assertTrue(is_array($account_as_array['account_types']));
        $this->assertEmpty($account_as_array['account_types']);
    }

    public function testFindAccountWithAllTypesDisabled(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id, 'disabled'=>1]);

        // WHEN
        $account = Account::find_account_with_types($generated_account->id);

        // THEN
        $this->assertNotEmpty($account);
        $account_as_array = $account->toArray();
        $this->assertArrayHasKey('id', $account_as_array);
        $this->assertArrayHasKey('account', $account_as_array);
        $this->assertArrayHasKey('total', $account_as_array);
        $this->assertArrayHasKey('account_types', $account_as_array);
        $this->assertTrue(is_array($account_as_array['account_types']));
        $this->assertEmpty($account_as_array['account_types']);
    }

    public function testFindAccountWhereAccountDoesNotExist(){
        // GIVEN - no account data provided
        $account_id = 9999;

        // WHEN
        $account = Account::find_account_with_types($account_id);

        // THEN
        $this->assertEmpty($account);
    }

}