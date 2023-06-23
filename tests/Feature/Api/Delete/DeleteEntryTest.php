<?php

namespace Tests\Feature\Api\Delete;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Entry;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class DeleteEntryTest extends TestCase {

    private string $_base_uri = '/api/entry/';

    public function testMarkingEntryDeleted() {
        // GIVEN
        $entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->create();

        // WHEN
        $get_response1 = $this->get($this->_base_uri.$entry->id);
        $delete_response = $this->delete($this->_base_uri.$entry->id);
        $get_response2 = $this->get($this->_base_uri.$entry->id);

        // THEN
        $this->assertResponseStatus($get_response1, HttpStatus::HTTP_OK);
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertResponseStatus($get_response2, HttpStatus::HTTP_NOT_FOUND);

        $this->assertEmpty($delete_response->getContent());
        $this->assertIsArray($get_response2->json());
        $this->assertEmpty($get_response2->json());

        // confirm disabled_stamp and modified_stamp have been updated
        $newly_disabled_entry = Entry::withTrashed()->find($entry->id);
        $this->assertNotNull($newly_disabled_entry->{Entry::DELETED_AT}, Entry::DELETED_AT.' value is unexpected ['.$newly_disabled_entry->{Entry::DELETED_AT}.']');
        $this->assertDateFormat($newly_disabled_entry->{Entry::DELETED_AT}, Carbon::ATOM, $newly_disabled_entry->{Entry::DELETED_AT}. "does not match format ".Carbon::ATOM);
        $this->assertNotEmpty($entry->{Entry::UPDATED_AT}, Entry::UPDATED_AT.' value is empty ['.$newly_disabled_entry->{Entry::UPDATED_AT}.']');
        $this->assertNotEmpty($newly_disabled_entry->{Entry::UPDATED_AT}, Entry::UPDATED_AT.' value is empty ['.$newly_disabled_entry->{Entry::UPDATED_AT}.']');
        $this->assertDateFormat($newly_disabled_entry->{Entry::UPDATED_AT}, Carbon::ATOM, $newly_disabled_entry->{Entry::UPDATED_AT}. "does not match format ".Carbon::ATOM);
        $this->assertNotEquals($entry->{Entry::UPDATED_AT}, $newly_disabled_entry->{Entry::UPDATED_AT});
    }

    public function testMarkingEntryDeletedWhenEntryDoesNotExist() {
        // GIVEN
        $entry_id = fake()->randomNumber();

        // WHEN
        $get_response = $this->get($this->_base_uri.$entry_id);
        $delete_response = $this->delete($this->_base_uri.$entry_id);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertIsArray($get_response->json());
        $this->assertEmpty($get_response->json());
        $this->assertEmpty($delete_response->getContent());
    }

}
