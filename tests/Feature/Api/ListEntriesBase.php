<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Entry;
use App\Models\Tag;
use App\Traits\EntryFilterKeys;
use App\Traits\MaxEntryResponseValue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ListEntriesBase extends TestCase {
    use EntryFilterKeys;
    use MaxEntryResponseValue;
    use WithFaker;

    const MIN_TEST_ENTRIES = 4;

    /**
     * @var Account
     */
    protected $_generated_account;

    /**
     * @var Collection[Tag]
     */
    protected $_generated_tags;

    protected string $_uri = '/api/entries';

    public function setUp(): void {
        parent::setUp();
        $this->_generated_account = Account::factory()->create();
        $this->_generated_tags = Tag::factory()->count($this->faker->randomDigitNotZero())->create();
    }

    /**
     * @param int $account_type_id
     * @param bool $entry_disabled
     * @param array $override_entry_components
     * @return Entry
     */
    protected function generate_entry_record(int $account_type_id, bool $entry_disabled, array $override_entry_components=[]): Entry {
        $default_entry_data = ['account_type_id'=>$account_type_id, 'disabled'=>$entry_disabled];
        $new_entry_data = array_merge($default_entry_data, $override_entry_components);
        unset($new_entry_data['tags']);
        unset($new_entry_data['has_attachments']);
        if ($new_entry_data['disabled']) {
            $new_entry_data['disabled_stamp'] = new Carbon();
        }
        /** @var Entry $generated_entry */
        $generated_entry = Entry::factory()->create($new_entry_data);

        if (!$entry_disabled) {    // no sense cluttering up the database with test data for something that isn't supposed to appear anyway
            if (isset($override_entry_components['has_attachments']) && $override_entry_components['has_attachments'] === true) {
                $generate_attachment_count = 1;
            } elseif (isset($override_entry_components['has_attachments']) && $override_entry_components['has_attachments'] === false) {
                $generate_attachment_count = 0;
            } else {
                $generate_attachment_count = $this->faker->randomDigitNotZero();
            }
            Attachment::factory()->count($generate_attachment_count)->create(['entry_id' => $generated_entry->id]);

            if (isset($override_entry_components['tags'])) {
                $generated_entry->tags()->sync($override_entry_components['tags']);
            } else {
                $assign_tag_to_entry_count = $this->faker->numberBetween(0, $this->_generated_tags->count());
                for ($j = 0; $j < $assign_tag_to_entry_count; $j++) {
                    $randomly_selected_tag = $this->_generated_tags->random();
                    $generated_entry->tags()->syncWithoutDetaching([$randomly_selected_tag->id]);
                }
            }
        }

        return $generated_entry;
    }

    /**
     * @param int   $totalEntriesToCreate
     * @param int   $account_type_id
     * @param array $filter_details
     * @param bool  $randomly_mark_entries_disabled
     * @param bool  $mark_entries_disabled
     *
     * @return Collection
     */
    protected function batch_generate_entries(int $totalEntriesToCreate, int $account_type_id, array $filter_details=[], bool $randomly_mark_entries_disabled=false, bool $mark_entries_disabled=false) {
        $generated_entries = collect();
        for ($i= 0; $i < $totalEntriesToCreate; $i++) {
            $generated_entry = $this->generate_entry_record(
                $account_type_id,
                ($randomly_mark_entries_disabled ? $this->faker->boolean() : $mark_entries_disabled),
                $filter_details
            );
            $generated_entries->push($generated_entry);
        }
        return $generated_entries;
    }

    /**
     * @param array $filters
     * @return array
     */
    protected function convert_filters_to_entry_components(array $filters): array {
        $entry_components = [];
        foreach ($filters as $filter_name => $constraint) {
            switch ($filter_name) {
                case self::$FILTER_KEY_START_DATE:
                case self::$FILTER_KEY_END_DATE:
                    $entry_components['entry_date'] = $constraint;
                    break;
                case self::$FILTER_KEY_MIN_VALUE:
                case self::$FILTER_KEY_MAX_VALUE:
                    $entry_components['entry_value'] = $constraint;
                    break;
                case self::$FILTER_KEY_ACCOUNT_TYPE:
                    $entry_components['account_type_id'] = $constraint;
                    break;
                case self::$FILTER_KEY_EXPENSE:
                    if ($constraint === true) {
                        $entry_components[$filter_name] = 1;
                    } elseif ($constraint === false) {
                        $entry_components[$filter_name] = 0;
                    }
                    break;
                case self::$FILTER_KEY_UNCONFIRMED:
                    if ($constraint === true) {
                        $entry_components['confirm'] = 0;
                    }
                    break;
                case self::$FILTER_KEY_ATTACHMENTS:
                    $entry_components['has_attachments'] = $constraint;
                    break;
                case self::$FILTER_KEY_TAGS:
                    $entry_components[$filter_name] = is_array($constraint) ? $constraint : [$constraint];
                    break;
                case self::$FILTER_KEY_IS_TRANSFER:
                    if ($constraint === true) {
                        $entry_components['transfer_entry_id'] = $this->faker->randomDigitNotNull;
                    } elseif ($constraint === false) {
                        $entry_components['transfer_entry_id'] = null;
                    }
                    break;
            }
        }
        return $entry_components;
    }

    /**
     * @param array $entry_nodes
     */
    protected function assertEntryNodesExist(array $entry_nodes) {
        $this->assertArrayHasKey('id', $entry_nodes);
        $this->assertArrayHasKey('entry_date', $entry_nodes);
        $this->assertArrayHasKey('entry_value', $entry_nodes);
        $this->assertArrayHasKey('memo', $entry_nodes);
        $this->assertArrayHasKey('account_type_id', $entry_nodes);
        $this->assertArrayHasKey('expense', $entry_nodes);
        $this->assertArrayHasKey('confirm', $entry_nodes);
        $this->assertArrayHasKey('disabled', $entry_nodes);
        $this->assertArrayHasKey('create_stamp', $entry_nodes);
        $this->assertArrayHasKey('modified_stamp', $entry_nodes);
        $this->assertArrayHasKey('disabled_stamp', $entry_nodes);
        $this->assertArrayHasKey('has_attachments', $entry_nodes);
        $this->assertArrayHasKey('tags', $entry_nodes);
        $this->assertArrayHasKey('is_transfer', $entry_nodes);
    }

    /**
     * @param array $entry_nodes
     * @param Entry $generated_entry
     */
    protected function assertEntryNodesMatchGeneratedEntry(array $entry_nodes, $generated_entry) {
        $failure_msg = "generated entry:".json_encode($generated_entry)."\nresponse entry:".json_encode($entry_nodes);
        $this->assertEquals($generated_entry->entry_date, $entry_nodes['entry_date'], $failure_msg);
        $this->assertEquals($generated_entry->entry_value, $entry_nodes['entry_value'], $failure_msg);
        $this->assertEquals($generated_entry->memo, $entry_nodes['memo'], $failure_msg);
        $this->assertEquals($generated_entry->account_type_id, $entry_nodes['account_type_id'], $failure_msg);
        $this->assertEquals($generated_entry->expense, $entry_nodes['expense'], $failure_msg);
        $this->assertEquals($generated_entry->confirm, $entry_nodes['confirm'], $failure_msg);
        $this->assertFalse($entry_nodes['disabled'], $failure_msg);    // this will always be false because we only display non-disabled entries
        $this->assertDateFormat($entry_nodes['create_stamp'], Carbon::ATOM, $failure_msg);
        $this->assertDatetimeWithinOneSecond($generated_entry->create_stamp, $entry_nodes['create_stamp'], $failure_msg);
        $this->assertDateFormat($entry_nodes['modified_stamp'], Carbon::ATOM, $failure_msg);
        $this->assertDatetimeWithinOneSecond($generated_entry->modified_stamp, $entry_nodes['modified_stamp'], $failure_msg);
        $this->assertNull($entry_nodes['disabled_stamp'], $failure_msg);    // this will always be null because we only display non-disabled entries
        $this->assertTrue(is_bool($entry_nodes['has_attachments']), $failure_msg);
        $this->assertEquals($generated_entry->has_attachments(), $entry_nodes['has_attachments'], $failure_msg);
        $this->assertTrue(is_array($entry_nodes['tags']), $failure_msg);
        $this->assertEqualsCanonicalizing($generated_entry->get_tag_ids(), $entry_nodes['tags'], $failure_msg);
        $this->assertTrue(is_bool($entry_nodes['is_transfer']), $failure_msg);
        $this->assertEquals(!is_null($generated_entry['transfer_entry_id']), $entry_nodes['is_transfer'], $failure_msg);
    }

    /**
     * @param int $generate_entry_count
     * @param array $entries_in_response
     * @param Collection $generated_entries
     * @param $generated_disabled_entries
     * @param string $sort_parameter
     * @param string $sort_direction
     */
    protected function runEntryListAssertions(int $generate_entry_count, array $entries_in_response, $generated_entries, $generated_disabled_entries=[], string $sort_parameter=Entry::DEFAULT_SORT_PARAMETER, string $sort_direction=Entry::DEFAULT_SORT_DIRECTION) {
        $this->assertcount($generate_entry_count, $entries_in_response);

        $previous_entry_in_response = null;
        foreach ($entries_in_response as $entry_in_response) {
            $this->assertArrayHasKey('id', $entry_in_response);
            $this->assertNotContains(
                $entry_in_response['id'],
                $generated_disabled_entries,
                'entry ID:'.$entry_in_response['id']."\ndisabled entries:".json_encode($generated_disabled_entries)."\nresponse entries:".json_encode($entries_in_response)
            );
            $generated_entry = $generated_entries->where('id', $entry_in_response['id'])->first();
            $this->assertNotEmpty($generated_entry, "Entry in response: ".json_encode($entry_in_response)." not found in generated set: ".$generated_entries->toJson());
            $this->assertInstanceOf(Entry::class, $generated_entry);
            $this->assertEntryNodesExist($entry_in_response);
            $this->assertEntryNodesMatchGeneratedEntry($entry_in_response, $generated_entry);

            // testing sort order
            if (!is_null($previous_entry_in_response)) {
                if ($sort_direction == Entry::SORT_DIRECTION_DESC) {
                    $this->assertGreaterThanOrEqual($entry_in_response[$sort_parameter], $previous_entry_in_response[$sort_parameter]);
                } elseif ($sort_direction == Entry::SORT_DIRECTION_ASC) {
                    $this->assertLessThanOrEqual($entry_in_response[$sort_parameter], $previous_entry_in_response[$sort_parameter]);
                }
            }
            $previous_entry_in_response = $entry_in_response;
        }
    }

}
