<?php

namespace Tests\Feature\Api\Get;

use App\Models\AccountType;
use App\Models\Entry;
use App\Traits\MaxEntryResponseValue;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\Feature\Api\ListEntriesBase;

class GetEntriesTest extends ListEntriesBase {
    use MaxEntryResponseValue;

    public function testGetEntriesThatDoNotExist() {
        // GIVEN - no data in database

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_NOT_FOUND);
        $response_body_as_array = $response->json();
        $this->assertIsArray($response_body_as_array);
        $this->assertEmpty($response_body_as_array);
    }

    public function testGetEntries() {
        // GIVEN
        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();

        $generate_entry_count = fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id, [], true);
        $generated_disabled_entries = $generated_entries->where('disabled', 1);
        if ($generated_disabled_entries->count() > 0) {   // if there are no disabled entries, then there is no need to do any fancy filtering
            $generated_entries = $generated_entries->sortByDesc('disabled') // sorting so disabled entries are at the start of the collection
                ->splice($generated_disabled_entries->count()-1);
            $generate_entry_count -= $generated_disabled_entries->count();
        }

        if ($generate_entry_count < 1) {
            // if we only generate entries that have been marked "disabled"
            // then we should create at least one entry is NOT marked "disabled
            $generated_entry = $this->batch_generate_entries(1, $generated_account_type->id)->first();
            $generated_entries->push($generated_entry);
            $generate_entry_count = 1;
        }

        // WHEN
        $response = $this->get($this->_uri);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK);
        $response_body_as_array = $response->json();
        $this->assertArrayHasKey('count', $response_body_as_array);
        $this->assertEquals($generate_entry_count, $response_body_as_array['count']);
        unset($response_body_as_array['count']);

        $this->runEntryListAssertions($generate_entry_count, $response_body_as_array, $generated_entries, $generated_disabled_entries->pluck('id'));
    }

    public function testGetEntriesByPage() {
        $page_limit = 3;
        // GIVEN
        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $generate_entry_count = fake()->numberBetween(($page_limit-1)*self::$MAX_ENTRIES_IN_RESPONSE+1, $page_limit*self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id);

        $entries_in_response = [];
        for ($i=0; $i<$page_limit; $i++) {
            // WHEN
            $response = $this->get($this->_uri.'/'.$i);

            // THEN
            $response->assertStatus(HttpStatus::HTTP_OK);
            $response_body_as_array = $response->json();

            $this->assertTrue(is_array($response_body_as_array));
            $this->assertArrayHasKey('count', $response_body_as_array);
            $this->assertEquals($generate_entry_count, $response_body_as_array['count']);
            unset($response_body_as_array['count']);

            if ($i+1 == $page_limit) {
                $this->assertCount($generate_entry_count - (($page_limit - 1) * self::$MAX_ENTRIES_IN_RESPONSE), $response_body_as_array);
            } else {
                $this->assertCount(self::$MAX_ENTRIES_IN_RESPONSE, $response_body_as_array);
            }

            $entries_in_response = array_merge($entries_in_response, $response_body_as_array);
        }

        $this->runEntryListAssertions($generate_entry_count, $entries_in_response, $generated_entries);
    }

    public function providerLargeDataSets(): array {
        $counts = [200, 500, 1000, 2000, 5000, 10000, 20000, 25000];
        $data_sets = [];
        foreach ($counts as $c) {
            $data_sets[$c.' entry records'] = [$c];
        }
        return $data_sets;
    }

    /**
     * @dataProvider providerLargeDataSets
     */
    public function testLargeDataSets(int $entry_count) {
        // GIVEN
        $table = with(new Entry())->getTable();
        /** @var AccountType $generated_account_type */
        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $generated_entries = Entry::factory()
            ->count(self::$MAX_ENTRIES_IN_RESPONSE)
            ->for($generated_account_type)
            ->state(['disabled'=>false])
            ->make()
            ->map(function(Entry $entry) {
                $entry->makeHidden('account_type', 'accountType');
                return $entry;
            })->toArray();
        // generating entries in batches and using database insert methods because it's faster
        for ($i=0; $i<($entry_count/self::$MAX_ENTRIES_IN_RESPONSE); $i++) {
            DB::table($table)->insert($generated_entries);
        }

        // WHEN
        $response = $this->get($this->_uri);
        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK); // this is the MOST IMPORTANT check. it confirms we were able to handle a large data set

        $response_as_array = $response->json();
        $this->assertEquals($entry_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->assertCount(self::$MAX_ENTRIES_IN_RESPONSE, $response_as_array);
    }

}
