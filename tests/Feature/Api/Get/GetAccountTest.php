<?php

namespace Tests\Feature\Api;

use App\Helpers\CurrencyHelper;
use Faker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Symfony\Component\HttpFoundation\Response;

use App\Account;
use App\AccountType;

class GetAccountTest extends TestCase {

    /**
     * @var string
     */
    private $_base_uri = '/api/account/';

    /**
     * @var Faker\Generator
     */
    private $_faker;

    public function setUp(){
        parent::setUp();
        $this->_faker = Faker\Factory::create();
    }

    public function testGetAccountData(){
        // GIVEN
        $account_type_count = $this->_faker->randomDigitNotNull;
        $generated_account = factory(Account::class)->create();
        $generated_account_types = factory(AccountType::class, $account_type_count)->create(['account_id'=>$generated_account->id]);
        // These nodes are not in the response output. Lets hide them from the object collection
        $generated_account_types->makeHidden(['account_id', 'last_updated']);

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
        $account_type_count = $this->_faker->randomDigitNotNull;
        $generated_account = factory(Account::class)->create();
        $generated_account_types = factory(AccountType::class, $account_type_count)->create(['account_id'=>$generated_account->id]);
        $generated_disabled_account_type = factory(AccountType::class)->create(['account_id'=>$generated_account->id, 'disabled'=>true]);
        $account_type_count++;
        $generated_account_types->push($generated_disabled_account_type);
        // These nodes are not in the response output. Lets hide them from the object collection
        $generated_account_types->makeHidden(['account_id', 'last_updated']);

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
        $account_type_count = $this->_faker->randomDigitNotNull;
        $generated_account = factory(Account::class)->create();
        factory(AccountType::class, $account_type_count)->create(['account_id'=>$generated_account->id, 'disabled'=>true]);

        // WHEN
        $response = $this->get($this->_base_uri.$generated_account->id);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $this->assertAccountDetailsOK($response->json(), $generated_account, $account_type_count);
    }

    public function testGetAccountDataWhenNoAccountDataExists(){
        // GIVEN - no database records are created

        // WHEN
        $response = $this->get($this->_base_uri.$this->_faker->randomDigitNotNull);

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

        $element = 'id';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account->id);
        unset($response_as_array[$element]);

        $element = 'name';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account->name);
        unset($response_as_array[$element]);

        $element = 'institution_id';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account->institution_id);
        unset($response_as_array[$element]);

        $element = 'disabled';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertTrue(is_bool($response_as_array[$element]));
        $this->assertEquals($response_as_array[$element], $generated_account->disabled);
        // Can't unset the 'disabled' element until the end

        $element = 'total';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account->total);
        unset($response_as_array[$element]);

        $element = 'currency';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals(3, strlen($response_as_array[$element]));
        $this->assertTrue(in_array($response_as_array[$element], CurrencyHelper::getCodesAsArray()));
        $this->assertEquals($response_as_array[$element], $generated_account->currency);
        unset($response_as_array[$element]);

        $element = 'account_types';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertTrue(is_array($response_as_array[$element]));
        $this->assertCount($account_type_count, $response_as_array[$element]);
        unset($response_as_array[$element]);

        $element = 'create_stamp';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertDateFormat($response_as_array[$element], DATE_ATOM, $response_as_array[$element]." not in correct format");
        unset($response_as_array[$element]);

        $element = 'modified_stamp';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertDateFormat($response_as_array[$element], DATE_ATOM, $response_as_array[$element]." not in correct format");
        unset($response_as_array[$element]);

        $element = 'disabled_stamp';
        $this->assertArrayHasKey($element, $response_as_array);
        if($response_as_array['disabled']){
            $this->assertDateFormat($response_as_array[$element], DATE_ATOM, $response_as_array[$element]." not in correct format");
        } else {
            $this->assertNull($response_as_array[$element]);
        }
        unset($response_as_array['disabled'], $response_as_array[$element]);

        $this->assertEmpty($response_as_array, "Unknown nodes found in JSON:".json_encode($response_as_array));
    }

    /**
     * @param array $account_types_in_response
     * @param AccountType $generated_account_types
     */
    private function assertAccountTypesOK($account_types_in_response, $generated_account_types){
        $generated_account_types_as_array = $generated_account_types->toArray();
        foreach($account_types_in_response as $account_type_in_response){
            $error_msg = "Factory generate account in JSON: ".json_encode($generated_account_types_as_array)."\nResponse Body component:".json_encode($account_type_in_response);
            $this->assertArrayHasKey('id', $account_type_in_response, $error_msg);
            $this->assertArrayHasKey('type', $account_type_in_response, $error_msg);
            $this->assertArrayHasKey('name', $account_type_in_response, $error_msg);
            $this->assertArrayHasKey('last_digits', $account_type_in_response, $error_msg);
            $this->assertArrayHasKey('disabled', $account_type_in_response, $error_msg);
            $this->assertArrayHasKey('create_stamp', $account_type_in_response, $error_msg);
            $this->assertArrayHasKey('modified_stamp', $account_type_in_response, $error_msg);
            $this->assertArrayHasKey('disabled_stamp', $account_type_in_response, $error_msg);
            $this->assertTrue(in_array($account_type_in_response, $generated_account_types_as_array), $error_msg);
        }
    }

}