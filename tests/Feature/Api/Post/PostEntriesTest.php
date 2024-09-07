<?php

namespace Tests\Feature\Api\Post;

use App\Models\AccountType;
use App\Models\Entry;
use App\Models\Tag;
use App\Traits\Tests\GenerateFilterTestCases;
use App\Traits\MaxEntryResponseValue;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Tests\Feature\Api\ListEntriesBase;

class PostEntriesTest extends ListEntriesBase {
    use GenerateFilterTestCases;
    use MaxEntryResponseValue;

    public static function providerPostEntriesFilter(): array {
        return self::generateFilterTestCases(fake());
    }

    /**
     * @dataProvider providerPostEntriesFilter
     */
    public function testPostEntriesThatDoNotExist(array $filter_details) {
        // GIVEN - no entries exist
        AccountType::factory()->for($this->_generated_account)->create();
        $filter_details = $this->setTestSpecificFilters(fake(), $filter_details, $this->_generated_account, $this->_generated_tags);

        $this->assertPostEntriesNotFound($filter_details);
    }

    /**
     * @dataProvider providerPostEntriesFilter
     */
    public function testPostEntries(array $filter_details) {
        // GIVEN
        $generate_entry_count = fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        /** @var AccountType $generated_account_type */
        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $filter_details = $this->setTestSpecificFilters(fake(), $filter_details, $this->_generated_account, $this->_generated_tags);

        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details), true);
        $generated_disabled_entries = $generated_entries->whereNotNull(Entry::DELETED_AT);
        if ($generated_disabled_entries->count() > 0) {   // if there are no disabled entries, then there is no need to do any fancy filtering
            $generated_entries = $generated_entries->whereNull(Entry::DELETED_AT);
            $generate_entry_count -= $generated_disabled_entries->count();
        }

        if ($generate_entry_count < 1) {
            // if we only generate entries that have been marked "disabled"
            // then we should create at least one entry is NOT marked "disabled
            $generated_entry = $this->batch_generate_entries(1, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details))->first();
            $generated_entries->push($generated_entry);
            $generate_entry_count = 1;
        }

        // WHEN
        $response = $this->postJson($this->_uri, $filter_details);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, "Filter:".json_encode($filter_details));
        $response_as_array = $response->json();
        $this->assertEquals($generate_entry_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions($generate_entry_count, $response_as_array, $generated_entries, $generated_disabled_entries->pluck('id'));
    }

    /**
     * @dataProvider providerPostEntriesFilter
     */
    public function testPostEntriesByPage(array $filter_details) {
        $page_limit = 3;
        // GIVEN
        $generate_entry_count = fake()->numberBetween(($page_limit - 1) * self::$MAX_ENTRIES_IN_RESPONSE + 1, $page_limit * self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $filter_details = $this->setTestSpecificFilters(fake(), $filter_details, $this->_generated_account, $this->_generated_tags);
        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        $entries_in_response = [];
        for ($i = 0; $i < $page_limit; $i++) {
            // WHEN
            $response = $this->postJson($this->_uri.'/'.$i, $filter_details);

            // THEN
            $this->assertResponseStatus($response, HttpStatus::HTTP_OK, "Filter:".json_encode($filter_details));
            $response_body_as_array = $response->json();

            $this->assertIsArray($response_body_as_array);
            $this->assertArrayHasKey('count', $response_body_as_array);
            $this->assertEquals($generate_entry_count, $response_body_as_array['count']);
            unset($response_body_as_array['count']);

            if ($i + 1 == $page_limit) {
                $this->assertCount($generate_entry_count - (($page_limit - 1) * self::$MAX_ENTRIES_IN_RESPONSE), $response_body_as_array);
            } else {
                $this->assertCount(self::$MAX_ENTRIES_IN_RESPONSE, $response_body_as_array);
            }

            $entries_in_response = array_merge($entries_in_response, $response_body_as_array);
        }

        $this->runEntryListAssertions($generate_entry_count, $entries_in_response, $generated_entries);
    }

    public function testPostEntriesFilterWithMultipleTagIdsAssignedToOneEntry() {
        // GIVEN
        $min_number_of_tags = 2;
        while ($this->_generated_tags->count() < $min_number_of_tags) {
            $this->_generated_tags = Tag::factory()->count(fake()->randomDigitNotZero())->create();
        }
        $tag_ids = $this->_generated_tags->pluck('id')->toArray();
        $filter_details[self::$FILTER_KEY_TAGS] = fake()->randomElements($tag_ids, fake()->numberBetween($min_number_of_tags, count($tag_ids)));
        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $generated_entries = $this->batch_generate_entries(1, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        // WHEN
        $response = $this->postJson($this->_uri, $filter_details);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertCount($response_as_array['count'], $generated_entries);
        unset($response_as_array['count']);
        $this->runEntryListAssertions(count($generated_entries), $response_as_array, $generated_entries);
    }

    public function testPostEntriesFilterWithStartDateGreaterThanEndDate() {
        // GIVEN
        $start_date = fake()->date();
        do {
            $end_date = fake()->date("Y-m-d", $start_date);  // second parameter guarantees $start_date is >= $end_date
        } while ($start_date < $end_date);
        $filter_details = [
            self::$FILTER_KEY_START_DATE => $start_date,
            self::$FILTER_KEY_END_DATE => $end_date,
        ];

        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $this->batch_generate_entries(fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE), $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));
        $this->assertPostEntriesNotFound($filter_details);
    }

    public function testPostEntriesFilterWithEndDateGreaterThanStartDate() {
        // GIVEN
        $end_date = fake()->date();
        do {
            $start_date = fake()->date("Y-m-d", $end_date); // second parameter guarantees $start_date is <= $end_date
        } while ($start_date > $end_date);
        $filter_details = [
            self::$FILTER_KEY_START_DATE => $start_date,
            self::$FILTER_KEY_END_DATE => $end_date,
        ];

        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $generated_entries_count = fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_entries = $this->batch_generate_entries($generated_entries_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        // WHEN
        $response = $this->postJson($this->_uri, $filter_details);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertEquals($generated_entries_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions($generated_entries_count, $response_as_array, $generated_entries);
    }

    public function testPostEntriesFilterWithMinValueGreaterThanMaxValue() {
        // GIVEN
        $min_value = fake()->randomFloat(2, 0, 50);
        do {
            $max_value = fake()->randomFloat(2, 0, $min_value);
        } while ($min_value < $max_value);
        $filter_details = [
            self::$FILTER_KEY_MIN_VALUE => $min_value,
            self::$FILTER_KEY_MAX_VALUE => $max_value,
        ];

        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $this->batch_generate_entries(fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE), $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));
        $this->assertPostEntriesNotFound($filter_details);
    }

    public function testPostEntriesFilterWithMaxValueGreaterThanMinValue() {
        // GIVEN
        $max_value = fake()->randomFloat(2, 0, 50);
        do {
            $min_value = fake()->randomFloat(2, 0, $max_value);
        } while ($min_value > $max_value);
        $filter_details = [
            self::$FILTER_KEY_MIN_VALUE => $min_value,
            self::$FILTER_KEY_MAX_VALUE => $max_value,
        ];

        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $generated_entries_count = fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_entries = $this->batch_generate_entries($generated_entries_count, $generated_account_type->id, $this->convert_filters_to_entry_components($filter_details));

        // WHEN
        $response = $this->postJson($this->_uri, $filter_details);

        // THEN
        $response->assertStatus(HttpStatus::HTTP_OK);
        $response_as_array = $response->json();
        $this->assertEquals($generated_entries_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions($generated_entries_count, $response_as_array, $generated_entries);
    }

    public function testPostEntriesFilterSort() {
        // GIVEN
        $generate_entry_count = fake()->numberBetween(self::MIN_TEST_ENTRIES, self::$MAX_ENTRIES_IN_RESPONSE);
        $generated_account_type = AccountType::factory()->for($this->_generated_account)->create();
        $generated_entries = $this->batch_generate_entries($generate_entry_count, $generated_account_type->id, []);
        // how we intend to sort
        $sort_options = Entry::get_fields_required_for_creation();
        unset($sort_options['memo']);   // can't and don't intend to sort by entry memo
        $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_PARAMETER] = fake()->randomElement($sort_options);
        $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_DIRECTION] = fake()->randomElement([Entry::SORT_DIRECTION_ASC, Entry::SORT_DIRECTION_DESC]);

        // WHEN
        $response = $this->postJson($this->_uri, $filter_details);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_OK, "Filter:".json_encode($filter_details));
        $response_as_array = $response->json();
        $this->assertEquals($generate_entry_count, $response_as_array['count']);
        unset($response_as_array['count']);
        $this->runEntryListAssertions(
            $generate_entry_count,
            $response_as_array,
            $generated_entries,
            [],
            $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_PARAMETER],
            $filter_details[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_DIRECTION]
        );
    }

    private function assertPostEntriesNotFound(array $filter_details): void {
        // WHEN
        $response = $this->postJson($this->_uri, $filter_details);

        // THEN
        $this->assertResponseStatus($response, HttpStatus::HTTP_NOT_FOUND, "Filter:".json_encode($filter_details));
        $response_as_array = $response->json();
        $this->assertTrue(is_array($response_as_array));
        $this->assertEmpty($response_as_array);
    }

}
