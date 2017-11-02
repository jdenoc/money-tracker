<?php

namespace Tests\Feature\Api;

use App\AccountType;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GetAccountTypesTest extends TestCase {

    use DatabaseMigrations;

    private $_uri = '/api/account-types';

    public function testGetAccountTypesWhenNoAccountTypesExist(){
        // GIVEN - no account_types exist

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $this->assertTrue(is_array($response->json()));
        $this->assertEmpty($response->json());
    }

    public function testGetAccountTypes(){
        $faker = Factory::create();
        // GIVEN
        $account_type_count = $faker->randomDigitNotNull;
        $generated_account_types = [];
        for($i=0; $i<$account_type_count; $i++){
            $generated_account_type = factory(AccountType::class)->create(['disabled'=>$faker->boolean]);
            $generated_account_types[$generated_account_type->id] = $generated_account_type;
            unset($generated_account_type);
        }

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertTrue(is_array($response_as_array));
        $this->assertNotEmpty($response_as_array);
        $this->assertArrayHasKey('count', $response_as_array);
        $this->assertEquals($account_type_count, $response_as_array['count']);
        unset($response_as_array['count']);
        foreach($response_as_array as $account_type_in_response){
            $this->assertArrayHasKey('id', $account_type_in_response);
            $this->assertArrayHasKey('type', $account_type_in_response);
            $this->assertArrayHasKey('last_digits', $account_type_in_response);
            $this->assertArrayHasKey('type_name', $account_type_in_response);
            $this->assertArrayHasKey('account_id', $account_type_in_response);
            $this->assertArrayHasKey('disabled', $account_type_in_response);
            $this->assertArrayHasKey('create_stamp', $account_type_in_response);
            $this->assertArrayHasKey('modified_stamp', $account_type_in_response);
            $this->assertArrayHasKey('disabled_stamp', $account_type_in_response);

            $this->assertNotEmpty($generated_account_types[$account_type_in_response['id']]);
            $generated_account_type = $generated_account_types[$account_type_in_response['id']];
            $this->assertEquals($generated_account_type->toArray(), $account_type_in_response);
        }
    }

}