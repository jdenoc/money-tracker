<?php

namespace Tests\Feature\Api\Get;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Institution;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetAccountTypeTest extends TestCase {
    use WithFaker;

    private string $_base_uri = '/api/account-type/%d';

    public function testGetAccountTypeDataWhenNoAccountTypeDataExists() {
        // GIVEN - no database records are created

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $this->faker->randomDigitNotZero()));

        // THEN
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertTrue(is_array($response_body_as_array));
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetAccountTypeData() {
        // GIVEN
        $generated_institution = Institution::factory()->create();
        $generated_account = Account::factory()->create(['institution_id'=>$generated_institution->id]);
        $generated_account_type = AccountType::factory()->create(['account_id'=>$generated_account->id]);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_account_type->id));

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertAccountTypeDetailsOK($response_body_as_array, $generated_account_type);
    }

    /**
     * @param array       $response_as_array
     * @param AccountType $generated_account_type
     */
    private function assertAccountTypeDetailsOK(array $response_as_array, $generated_account_type) {
        $element = 'id';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account_type->id);
        unset($response_as_array[$element]);

        $element = 'name';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account_type->name);
        unset($response_as_array[$element]);

        $element = 'account_id';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account_type->account_id);
        unset($response_as_array[$element]);

        $element = 'disabled';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertTrue(is_bool($response_as_array[$element]));
        $this->assertEquals($response_as_array[$element], $generated_account_type->disabled);
        // Can't unset the 'disabled' element until the end

        $element = 'type';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account_type->type);
        unset($response_as_array[$element]);

        $element = 'last_digits';
        $this->assertArrayHasKey($element, $response_as_array);
        $this->assertEquals($response_as_array[$element], $generated_account_type->last_digits);
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
        if ($response_as_array['disabled']) {
            $this->assertDateFormat($response_as_array[$element], DATE_ATOM, $response_as_array[$element]." not in correct format");
        } else {
            $this->assertNull($response_as_array[$element]);
        }
        unset($response_as_array['disabled'], $response_as_array[$element]);

        $this->assertEmpty($response_as_array, "Unknown nodes found in JSON:".json_encode($response_as_array));
    }

}
