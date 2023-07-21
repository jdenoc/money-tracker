<?php

namespace Tests\Feature\Api\Delete;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Entry;
use App\Models\Institution;
use Brick\Money\Money;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\TestCase;

class DeleteEntryTest extends TestCase {

    private const BASE_URI = '/api/entry/%d';

    public function testMarkingEntryDeleted() {
        // GIVEN
        $entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->create();

        // WHEN
        $get_response1 = $this->get(sprintf(self::BASE_URI, $entry->id));

        // THEN
        $this->assertResponseStatus($get_response1, HttpStatus::HTTP_OK);

        // WHEN
        $delete_response = $this->delete(sprintf(self::BASE_URI, $entry->id));
        $get_response2 = $this->get(sprintf(self::BASE_URI, $entry->id));

        // THEN
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertResponseStatus($get_response2, HttpStatus::HTTP_OK);

        $this->assertEmpty($delete_response->getContent());
        $this->assertIsArray($get_response2->json());
        $this->assertNotEmpty($get_response2->json());

        // confirm disabled_stamp and modified_stamp have been updated
        $newly_disabled_entry = Entry::withTrashed()->find($entry->id);
        $this->assertEquals($newly_disabled_entry->{Entry::DELETED_AT}, $get_response2[Entry::DELETED_AT]);
        $this->assertNotNull($newly_disabled_entry->{Entry::DELETED_AT}, Entry::DELETED_AT.' value is unexpected ['.$newly_disabled_entry->{Entry::DELETED_AT}.']');
        $this->assertDateFormat($newly_disabled_entry->{Entry::DELETED_AT}, Carbon::ATOM, $newly_disabled_entry->{Entry::DELETED_AT}. "does not match format ".Carbon::ATOM);

        $this->assertEquals($newly_disabled_entry->{Entry::UPDATED_AT}, $get_response2[Entry::UPDATED_AT]);
        $this->assertNotEmpty($newly_disabled_entry->{Entry::UPDATED_AT}, Entry::UPDATED_AT.' value is empty ['.$newly_disabled_entry->{Entry::UPDATED_AT}.']');
        $this->assertDateFormat($newly_disabled_entry->{Entry::UPDATED_AT}, Carbon::ATOM, $newly_disabled_entry->{Entry::UPDATED_AT}. "does not match format ".Carbon::ATOM);
    }

    public function testMarkingPreviouslyDeletedEntryToBeDeleted() {
        // GIVEN
        $entry = Entry::factory()
            ->for(AccountType::factory()->for(Account::factory()))
            ->disabled()
            ->create();

        // WHEN
        $get_response = $this->get(sprintf(self::BASE_URI, $entry->id));

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $this->assertIsArray($get_response->json());
        $this->assertNotEmpty($get_response->json());
        $this->assertNotNull($get_response[Entry::DELETED_AT]);

        // WHEN
        $delete_response = $this->delete(sprintf(self::BASE_URI, $entry->id));

        // THEN
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($delete_response->getContent());
    }

    public function testMarkingEntryDeletedWhenEntryDoesNotExist() {
        // GIVEN
        $entry_id = fake()->randomNumber();

        // WHEN
        $get_response = $this->get(sprintf(self::BASE_URI, $entry_id));

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertIsArray($get_response->json());
        $this->assertEmpty($get_response->json());

        // WHEN
        $delete_response = $this->delete(sprintf(self::BASE_URI, $entry_id));

        // THEN
        $this->assertResponseStatus($delete_response, HttpStatus::HTTP_NOT_FOUND);
        $this->assertEmpty($delete_response->getContent());
    }

    public function testDeletingEntryUpdatesAccountTotal() {
        // GIVEN
        $generated_account = Account::factory()->for(Institution::factory())
            ->create();
        $generated_account_type = AccountType::factory()->for($generated_account)->create();
        $generated_entry = Entry::factory()->for($generated_account_type)
            ->create();

        // WHEN
        $get_account_response1 = $this->get('/api/account/'.$generated_account->id);
        // THEN
        $get_account_response1->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response1_as_array = $get_account_response1->json();
        $this->assertArrayHasKey('total', $get_account_response1_as_array);
        $this->assertArrayHasKey('currency', $get_account_response1_as_array);
        $original_total = Money::of($get_account_response1_as_array['total'], $get_account_response1_as_array['currency']);

        // WHEN
        $delete_response = $this->delete(sprintf(self::BASE_URI, $generated_entry->id));
        // THEN
        $delete_response->assertStatus(HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($delete_response->getContent());

        // WHEN
        $get_account_response2 = $this->get('/api/account/'.$generated_account->id);
        // THEN
        $get_account_response2->assertStatus(HttpStatus::HTTP_OK);
        $get_account_response2_as_array = $get_account_response2->json();
        $this->assertArrayHasKey('total', $get_account_response2_as_array);
        $this->assertArrayHasKey('currency', $get_account_response2_as_array);
        $updated_total = Money::of($get_account_response2_as_array['total'], $get_account_response2_as_array['currency']);

        $entry_value = Money::of($generated_entry->entry_value, $generated_account->currency)
            ->multipliedBy(($generated_entry->expense ? -1 : 1));
        $this->assertTrue(
            $original_total->minus($entry_value)->isEqualTo($updated_total),
            "Entry value:".$entry_value."; Original total:".$original_total."; Updated total:".$updated_total
        );
    }

}
