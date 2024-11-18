<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\Entry;
use App\Models\Tag;
use App\Traits\EntryFilterKeys;
use App\Traits\MaxEntryResponseValue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ListEntriesBase extends TestCase {
    use EntryFilterKeys;
    use MaxEntryResponseValue;

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
        $this->_generated_tags = Tag::factory()->count(fake()->randomDigitNotZero())->create();
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
        $entry_data = array_merge(['account_type_id'=>$account_type_id], $filter_details);
        unset($entry_data['tags'], $entry_data['has_attachments']);

        $generated_entries = Entry::factory()->count($totalEntriesToCreate)
            ->state($entry_data)
            ->state(new Sequence(function() use ($randomly_mark_entries_disabled, $mark_entries_disabled) {
                return [Entry::DELETED_AT => function() use ($randomly_mark_entries_disabled, $mark_entries_disabled) {
                    if ($randomly_mark_entries_disabled) {
                        $entry_disabled = function() { return fake()->boolean(); };
                    } else {
                        $entry_disabled = $mark_entries_disabled;
                    }

                    return $entry_disabled ? now() : null;
                }];
            }))
            ->create();

        $generated_entries->transform(function(Entry $entry) use ($filter_details) {
            if (is_null($entry->{Entry::DELETED_AT})) {  // no sense cluttering up the database with test data for something that isn't supposed to appear anyway
                $entry->is_transfer = !is_null($entry->transfer_entry_id);

                if (isset($filter_details['has_attachments']) && $filter_details['has_attachments'] === true) {
                    $generate_attachment_count = 1;
                } elseif (isset($filter_details['has_attachments']) && $filter_details['has_attachments'] === false) {
                    $generate_attachment_count = 0;
                } else {
                    $generate_attachment_count = fake()->randomDigitNotZero();
                }
                Attachment::factory()->count($generate_attachment_count)->for($entry)->create();

                if (isset($filter_details['tags'])) {
                    $entry->tags()->sync($filter_details['tags']);
                } else {
                    $assign_tag_to_entry_count = fake()->numberBetween(0, $this->_generated_tags->count());
                    $entry
                        ->tags()
                        ->syncWithoutDetaching($this->_generated_tags->random($assign_tag_to_entry_count)->pluck('id')->toArray());
                }
                $entry->tags = $entry->tagIds;

                $entry->makeHidden(['accountType', 'attachments', 'transfer_entry_id']);
            }
            return $entry;
        });

        return $generated_entries;
    }

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
                        $entry_components['transfer_entry_id'] = fake()->randomDigitNotNull;
                    } elseif ($constraint === false) {
                        $entry_components['transfer_entry_id'] = null;
                    }
                    break;
            }
        }
        return $entry_components;
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
        $expected_elements = ['id', 'entry_date', 'entry_value', 'memo', 'account_type_id', 'expense', 'confirm', Entry::CREATED_AT, Entry::UPDATED_AT, Entry::DELETED_AT, 'has_attachments', 'tags', 'is_transfer'];
        foreach ($entries_in_response as $entry_in_response) {
            $this->assertEqualsCanonicalizing($expected_elements, array_keys($entry_in_response));

            $this->assertNotContains(
                $entry_in_response['id'],
                $generated_disabled_entries,
                'entry ID:'.$entry_in_response['id']."\ndisabled entries:".json_encode($generated_disabled_entries)."\nresponse entries:".json_encode($entries_in_response)
            );

            /** @var Entry $generated_entry */
            $generated_entry = $generated_entries->where('id', $entry_in_response['id'])->first();
            $failure_msg = "generated entry:".json_encode($generated_entry)."\nresponse entry:".json_encode($entry_in_response);
            $this->assertNotEmpty($generated_entry, $failure_msg);

            $generated_entry_as_array = $generated_entry->toArray();
            $elements_to_pre_check = [Entry::CREATED_AT, Entry::UPDATED_AT, 'tags', 'has_attachments'];
            foreach ($elements_to_pre_check as $element) {
                switch($element) {
                    case Entry::CREATED_AT:
                    case Entry::UPDATED_AT:
                        $this->assertDateFormat($entry_in_response[$element], Carbon::ATOM, $failure_msg);
                        $this->assertDatetimeWithinOneSecond($generated_entry_as_array[$element], $entry_in_response[$element], $failure_msg);
                        break;
                    case 'tags':
                        $this->assertEqualsCanonicalizing($generated_entry_as_array[$element], $entry_in_response[$element], $failure_msg);
                        break;
                    case 'has_attachments':
                        $this->assertEquals($generated_entry->$element, $entry_in_response[$element], $failure_msg);
                        break;
                }
                unset($generated_entry_as_array[$element], $entry_in_response[$element]);
            }
            // compare the rest of the response
            $this->assertEquals($generated_entry_as_array, $entry_in_response, $failure_msg);

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
