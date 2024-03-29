<?php

namespace Tests\Feature\Api\Delete;

use App\Account;
use App\AccountType;
use App\Entry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class DeleteEntryTest extends TestCase {

    use WithFaker;

    private $_base_uri = '/api/entry/';

    public function testMarkingEntryDeleted(){
        // GIVEN
        $generated_account = factory(Account::class)->create();
        $generated_account_type = factory(AccountType::class)->create(['account_id'=>$generated_account->id]);
        $entry = factory(Entry::class)->create(['account_type_id'=>$generated_account_type->id]);

        // WHEN
        $get_response1 = $this->get($this->_base_uri.$entry->id);
        $delete_response = $this->delete($this->_base_uri.$entry->id);
        $get_response2 = $this->get($this->_base_uri.$entry->id);

        // THEN
        $this->assertResponseStatus($get_response1, HttpStatus::HTTP_OK);
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertResponseStatus($get_response2, HttpStatus::HTTP_NOT_FOUND);

        $this->assertEmpty($delete_response->getContent());
        $this->assertTrue(is_array($get_response2->json()));
        $this->assertEmpty($get_response2->json());

        // confirm disabled_stamp and modified_stamp have been updated
        $newly_disabled_entry = Entry::find($entry->id);
        $this->assertTrue($newly_disabled_entry->disabled);
        $this->assertNotEmpty($newly_disabled_entry->disabled_stamp);
        $this->assertDateFormat($newly_disabled_entry->disabled_stamp, Carbon::ATOM, $newly_disabled_entry->disabled_stamp. "does not match format ".Carbon::ATOM);
        $this->assertNotEmpty($entry->modified_stamp);
        $this->assertNotEmpty($newly_disabled_entry->modified_stamp);
        $this->assertDateFormat($newly_disabled_entry->modified_stamp, Carbon::ATOM, $newly_disabled_entry->modfied_stamp. "does not match format ".Carbon::ATOM);
        $this->assertNotEquals($entry->modified_stamp, $newly_disabled_entry->modfied_stamp);
    }

    public function testMarkingEntryDeletedWhenEntryDoesNotExist(){
        // GIVEN
        $entry_id = $this->faker->randomNumber();

        // WHEN
        $get_response = $this->get($this->_base_uri.$entry_id);
        $delete_response = $this->delete($this->_base_uri.$entry_id);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertTrue(is_array($get_response->json()));
        $this->assertEmpty($get_response->json());
        $this->assertEmpty($delete_response->getContent());
    }

}