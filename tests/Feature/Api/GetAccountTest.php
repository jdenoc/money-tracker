<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Account;
use App\AccountType;

class GetAccountTest extends TestCase {

    use DatabaseMigrations;

    public function testGetAccountData(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        $generated_account_types = factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id]);

        // WHEN
        $response = $this->get('/api/account/'.$generated_account->id);

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($response_body_as_array['id'], $generated_account->id);
        $this->assertArrayHasKey('account', $response_body_as_array);
        $this->assertEquals($response_body_as_array['account'], $generated_account->account);
        $this->assertArrayHasKey('total', $response_body_as_array);
        $this->assertEquals($response_body_as_array['total'], $generated_account->total);
        $this->assertArrayHasKey('account_types', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['account_types']));
        $this->assertEquals($account_type_count, count($response_body_as_array['account_types']));

        $account_types_array_count = 0;
        $generated_account_types_as_array = [];
        foreach($generated_account_types as $generated_account_type) {
            $generated_account_types_as_array[$account_types_array_count]['id'] = $generated_account_type->id;
            $generated_account_types_as_array[$account_types_array_count]['type'] = $generated_account_type->type;
            $generated_account_types_as_array[$account_types_array_count]['type_name'] = $generated_account_type->type_name;
            $generated_account_types_as_array[$account_types_array_count]['last_digits'] = $generated_account_type->last_digits;
            $account_types_array_count++;
        }

        foreach($response_body_as_array['account_types'] as $account_type_as_response){
            $this->assertArrayHasKey('id', $account_type_as_response);
            $this->assertArrayHasKey('type', $account_type_as_response);
            $this->assertArrayHasKey('type_name', $account_type_as_response);
            $this->assertArrayHasKey('last_digits', $account_type_as_response);
            $this->assertTrue(
                in_array($account_type_as_response, $generated_account_types_as_array),
                "Factory generate account in JSON: ".json_encode($generated_account_types_as_array)."\nResponse Body component:".json_encode($account_type_as_response)
            );
        }
    }

    public function testGetAccountDataWhenAnAccountTypesRecordIsDisabled(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        $generated_disabled_account_type = factory(AccountType::class)->create(['account_group'=>$generated_account->id, 'disabled'=>1]);
        $generated_account_types = factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id]);

        // WHEN
        $response = $this->get('/api/account/'.$generated_account->id);

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($response_body_as_array['id'], $generated_account->id);
        $this->assertArrayHasKey('account', $response_body_as_array);
        $this->assertEquals($response_body_as_array['account'], $generated_account->account);
        $this->assertArrayHasKey('total', $response_body_as_array);
        $this->assertEquals($response_body_as_array['total'], $generated_account->total);
        $this->assertArrayHasKey('account_types', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['account_types']));
        $this->assertEquals($account_type_count, count($response_body_as_array['account_types']));

        $account_types_array_count = 0;
        $generated_account_types_as_array = [];
        foreach($generated_account_types as $generated_account_type) {
            $generated_account_types_as_array[$account_types_array_count]['id'] = $generated_account_type->id;
            $generated_account_types_as_array[$account_types_array_count]['type'] = $generated_account_type->type;
            $generated_account_types_as_array[$account_types_array_count]['type_name'] = $generated_account_type->type_name;
            $generated_account_types_as_array[$account_types_array_count]['last_digits'] = $generated_account_type->last_digits;
            $account_types_array_count++;
        }

        foreach($response_body_as_array['account_types'] as $account_type_as_response){
            $this->assertArrayHasKey('id', $account_type_as_response);
            $this->assertArrayHasKey('type', $account_type_as_response);
            $this->assertArrayHasKey('type_name', $account_type_as_response);
            $this->assertArrayHasKey('last_digits', $account_type_as_response);
            $this->assertTrue(
                in_array($account_type_as_response, $generated_account_types_as_array),
                "Factory generate account in JSON: ".json_encode($generated_account_types_as_array)."\nResponse Body component:".json_encode($account_type_as_response)
            );
        }

        $disabled_account_type_as_array = [
            'id'=>$generated_disabled_account_type->id,
            'type'=>$generated_disabled_account_type->id,
            'type_name'=>$generated_disabled_account_type->type_name,
            'last_digits'=>$generated_disabled_account_type->last_digits,
            'last_updated'=>$generated_disabled_account_type->last_updated
        ];
        $this->assertTrue(
            !in_array($disabled_account_type_as_array, $response_body_as_array['account_types']),
            "Factory generate account in JSON: ".json_encode($disabled_account_type_as_array)."\nResponse Body:".$response_body
        );
    }

    public function testGetAccountDataWhenNoAccountTypeRecordsExist(){
        // GIVEN
        $generated_account = factory(Account::class)->create();

        // WHEN
        $response = $this->get('/api/account/'.$generated_account->id);

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($response_body_as_array['id'], $generated_account->id);
        $this->assertArrayHasKey('account', $response_body_as_array);
        $this->assertEquals($response_body_as_array['account'], $generated_account->account);
        $this->assertArrayHasKey('total', $response_body_as_array);
        $this->assertEquals($response_body_as_array['total'], $generated_account->total);
        $this->assertArrayHasKey('account_types', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['account_types']));
        $this->assertEmpty($response_body_as_array['account_types']);
    }

    public function testGetAccountDataWhenOnlyDisabledAccountTypeRecordsExist(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id, 'disabled'=>1]);

        // WHEN
        $response = $this->get('/api/account/'.$generated_account->id);

        // THEN
        $response->assertStatus(200);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertArrayHasKey('id', $response_body_as_array);
        $this->assertEquals($generated_account->id, $response_body_as_array['id']);
        $this->assertArrayHasKey('account', $response_body_as_array);
        $this->assertEquals($generated_account->account, $response_body_as_array['account']);
        $this->assertArrayHasKey('total', $response_body_as_array);
        $this->assertEquals($generated_account->total, $response_body_as_array['total']);
        $this->assertArrayHasKey('account_types', $response_body_as_array);
        $this->assertTrue(is_array($response_body_as_array['account_types']));
        $this->assertEmpty($response_body_as_array['account_types']);
    }

    public function testGetAccountDataWhenNoAccountDataExists(){
        // GIVEN - no database records are created
        $account_id = 99999;

        // WHEN
        $response = $this->get('/api/account/'.$account_id);

        // THEN
        $response->assertStatus(404);
        $response_body = $response->getContent();
        $response_body_as_array = json_decode($response_body, true);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

}