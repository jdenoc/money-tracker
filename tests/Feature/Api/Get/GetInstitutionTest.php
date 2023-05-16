<?php

namespace Tests\Feature\Api\Get;

use App\Models\Account;
use App\Models\Institution;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class GetInstitutionTest extends TestCase {

    protected string $_base_uri = '/api/institution/%d';

    public function testGetInstitutionWhenNoInstitutionExists() {
        // GIVEN - no institution
        $institution_id = fake()->randomDigitNotZero();

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $institution_id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetInstitutionWithoutAccounts() {
        // GIVEN
        /** @var Institution $generated_institution */
        $generated_institution = Institution::factory()->create();

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_institution->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertNotEmpty($response_body_as_array);

        $this->assertInstitutionNode($response_body_as_array, $generated_institution);
        $this->assertEmpty($response_body_as_array['accounts']);
    }

    public function testGetInstitution() {
        // GIVEN
        $generated_account_count = fake()->randomDigitNotZero();
        /** @var Institution $generated_institution */
        $generated_institution = Institution::factory()->create();
        $generated_accounts = Account::factory()->count($generated_account_count)->for($generated_institution)->create();
        // These nodes are not in the response output. Let's hide them from the object collection.
        $generated_accounts->makeHidden(['institution_id', Account::CREATED_AT, Account::UPDATED_AT, 'disabled_stamp']);

        // WHEN
        $response = $this->get(sprintf($this->_base_uri, $generated_institution->id));

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertNotEmpty($response_body_as_array);

        $this->assertInstitutionNode($response_body_as_array, $generated_institution);
        $this->assertNotEmpty($response_body_as_array['accounts']);
        $this->assertCount($generated_account_count, $response_body_as_array['accounts']);
        foreach ($response_body_as_array['accounts'] as $institution_account_in_response) {
            $this->assertInstitutionAccountNodesOK($institution_account_in_response, $generated_accounts);
        }
    }

    public function assertInstitutionNode(array $institute_in_response, Institution $generated_institution) {
        $expeceted_elements = ['id', 'name', 'active', Institution::CREATED_AT, Institution::UPDATED_AT, 'accounts'];
        $this->assertEqualsCanonicalizing($expeceted_elements, array_keys($institute_in_response));

        $failure_message = 'generated institution:'.json_encode($generated_institution)."\nresponse institution:".json_encode($institute_in_response);
        foreach ($expeceted_elements as $element) {
            switch ($element) {
                case Institution::CREATED_AT:
                case Institution::UPDATED_AT:
                    $this->assertDateFormat($institute_in_response[$element], Carbon::ATOM, $failure_message."\nfor these value to equal, PHP & MySQL timestamps must be the same");
                    break;
                case 'accounts':
                    $this->assertIsArray($institute_in_response[$element], $failure_message);
                    break;
                default:
                    $this->assertEquals($generated_institution->$element, $institute_in_response[$element]);
                    break;
            }
        }
    }

    public function assertInstitutionAccountNodesOK($institution_account_in_response, $generated_accounts) {
        $expected_elements = ['id', 'name', 'disabled', 'total', 'currency'];
        $this->assertEqualsCanonicalizing($expected_elements, array_keys($institution_account_in_response));

        $generated_account = $generated_accounts->where('id', $institution_account_in_response['id'])->first();
        $this->assertNotEmpty($generated_account);
        $this->assertEquals($generated_account->toArray(), $institution_account_in_response);
    }

}
