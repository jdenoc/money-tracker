<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;

use App\Account;
use App\AccountType;

class GetAccountTest extends TestCase {

    use DatabaseMigrations;

    private $_base_uri = '/api/account/';

    public function testGetAccountData(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        $generated_account_types = factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id]);

        // WHEN
        $response = $this->get($this->_base_uri.$generated_account->id);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertAccountDetailsOK($response_body_as_array, $generated_account, $account_type_count);
        $this->assertAccountTypesOK($response_body_as_array['account_types'], $generated_account_types);
    }

    public function testGetAccountDataWhenAnAccountTypesRecordIsDisabled(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        $generated_account_types = factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id]);
        $generated_disabled_account_type = factory(AccountType::class)->create(['account_group'=>$generated_account->id, 'disabled'=>true]);
        $account_type_count++;
        $generated_account_types->push($generated_disabled_account_type);

        // WHEN
        $response = $this->get($this->_base_uri.$generated_account->id);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertAccountDetailsOK($response_body_as_array, $generated_account, $account_type_count);
        $this->assertAccountTypesOK($response_body_as_array['account_types'], $generated_account_types);
    }

    public function testGetAccountDataWhenNoAccountTypeRecordsExist(){
        // GIVEN
        $generated_account = factory(Account::class)->create();

        // WHEN
        $response = $this->get($this->_base_uri.$generated_account->id);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $this->assertAccountDetailsOK($response->json(), $generated_account, 0);
    }

    public function testGetAccountDataWhenOnlyDisabledAccountTypeRecordsExist(){
        // GIVEN
        $account_type_count = 2;
        $generated_account = factory(Account::class)->create();
        factory(AccountType::class, $account_type_count)->create(['account_group'=>$generated_account->id, 'disabled'=>1]);

        // WHEN
        $response = $this->get($this->_base_uri.$generated_account->id);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $this->assertAccountDetailsOK($response->json(), $generated_account, $account_type_count);
    }

    public function testGetAccountDataWhenNoAccountDataExists(){
        // GIVEN - no database records are created
        $account_id = 99999;

        // WHEN
        $response = $this->get($this->_base_uri.$account_id);

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body_as_array = $this->getResponseAsArray($response);
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    /**
     * @param array $response_as_array
     * @param Account $generated_account
     * @param int $account_type_count
     */
    private function assertAccountDetailsOK($response_as_array, $generated_account, $account_type_count){
        $this->assertTrue(is_array($response_as_array));
        $this->assertArrayHasKey('id', $response_as_array);
        $this->assertEquals($response_as_array['id'], $generated_account->id);
        unset($response_as_array['id']);
        $this->assertArrayHasKey('name', $response_as_array);
        $this->assertEquals($response_as_array['name'], $generated_account->name);
        unset($response_as_array['name']);
        $this->assertArrayHasKey('institution_id', $response_as_array);
        $this->assertEquals($response_as_array['institution_id'], $generated_account->institution_id);
        unset($response_as_array['institution_id']);
        $this->assertArrayHasKey('disabled', $response_as_array);
        $this->assertEquals($response_as_array['disabled'], $generated_account->disabled);
        unset($response_as_array['disabled']);
        $this->assertArrayHasKey('total', $response_as_array);
        $this->assertEquals($response_as_array['total'], $generated_account->total);
        unset($response_as_array['total']);
        $this->assertArrayHasKey('account_types', $response_as_array);
        $this->assertTrue(is_array($response_as_array['account_types']));
        $this->assertCount($account_type_count, $response_as_array['account_types']);
        unset($response_as_array['account_types']);
        $this->assertEmpty($response_as_array, "Unknown nodes found in JSON:".json_encode($response_as_array));
    }

    /**
     * @param array $account_types_in_response
     * @param AccountType $generated_account_types
     */
    private function assertAccountTypesOK($account_types_in_response, $generated_account_types){
        $account_types_array_count = 0;
        $generated_account_types_as_array = [];
        foreach($generated_account_types as $generated_account_type) {
            $generated_account_types_as_array[$account_types_array_count]['id'] = $generated_account_type->id;
            $generated_account_types_as_array[$account_types_array_count]['type'] = $generated_account_type->type;
            $generated_account_types_as_array[$account_types_array_count]['type_name'] = $generated_account_type->type_name;
            $generated_account_types_as_array[$account_types_array_count]['last_digits'] = $generated_account_type->last_digits;
            $generated_account_types_as_array[$account_types_array_count]['disabled'] = $generated_account_type->disabled;
            $account_types_array_count++;
        }

        foreach($account_types_in_response as $account_type_in_response){
            $this->assertArrayHasKey('id', $account_type_in_response);
            $this->assertArrayHasKey('type', $account_type_in_response);
            $this->assertArrayHasKey('type_name', $account_type_in_response);
            $this->assertArrayHasKey('last_digits', $account_type_in_response);
            $this->assertArrayHasKey('disabled', $account_type_in_response);
            $this->assertTrue(
                in_array($account_type_in_response, $generated_account_types_as_array),
                "Factory generate account in JSON: ".json_encode($generated_account_types_as_array)."\nResponse Body component:".json_encode($account_type_in_response)
            );
        }

//        $disabled_account_type_as_array = [
//            'id'=>$generated_disabled_account_type->id,
//            'type'=>$generated_disabled_account_type->id,
//            'type_name'=>$generated_disabled_account_type->type_name,
//            'last_digits'=>$generated_disabled_account_type->last_digits,
//            'last_updated'=>$generated_disabled_account_type->last_updated
//        ];
//        $this->assertTrue(
//            !in_array($disabled_account_type_as_array, $response_body_as_array['account_types']),
//            "Factory generate account in JSON: ".json_encode($disabled_account_type_as_array)."\nResponse Body:".$response->getContent()
//        );
    }

}