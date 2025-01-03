<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use App\Models\AccountType;
use App\Models\Attachment;
use App\Traits\EntryFilterKeys;
use App\Traits\EntryResponseKeys;
use App\Traits\EntryTransferKeys;
use App\Traits\MaxEntryResponseValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OutOfRangeException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class EntryController extends Controller {
    use EntryFilterKeys;
    use EntryTransferKeys;
    use EntryResponseKeys;
    use MaxEntryResponseValue;

    /**
     * GET /api/entry/{entry_id}
     */
    public function get_entry(int $entry_id): Response {
        try {
            $entry = Entry::withTrashed()->with(['tags', 'attachments'])->findOrFail($entry_id);

            // we're not going to show disabled entries,
            // so why bother telling someone that something that isn't disabled
            $entry->makeHidden(['accountType']);
            $entry->tags->makeHidden('pivot');  // this is an artifact left over from the relationship logic
            $entry->attachments->makeHidden('entry_id');  // we already know the attachment is associated with this entry, no need to repeat that
            return response($entry, HttpStatus::HTTP_OK);
        } catch (\Exception $e) {
            return response([], HttpStatus::HTTP_NOT_FOUND);
        }
    }

    /**
     * GET /api/entries/{page}
     */
    public function get_paged_entries(int $page_number = 0): Response {
        return $this->provide_paged_entries_response([], $page_number);
    }

    /**
     * POST /api/entries/{page}
     */
    public function filter_paged_entries(Request $request, int $page_number = 0): Response {
        $post_body = $request->getContent();
        $filter_data = json_decode($post_body, true);

        if (empty($filter_data)) {
            return $this->provide_paged_entries_response([], $page_number);
        }

        if (empty($filter_data[self::$FILTER_KEY_SORT]) || !is_array($filter_data[self::$FILTER_KEY_SORT])) {
            $sort_by = Entry::DEFAULT_SORT_PARAMETER;
            $sort_direction = Entry::DEFAULT_SORT_DIRECTION;
        } else {
            $sort_by = empty($filter_data[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_PARAMETER]) ? Entry::DEFAULT_SORT_PARAMETER : $filter_data[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_PARAMETER];
            if (empty($filter_data[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_DIRECTION]) || !in_array($filter_data[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_DIRECTION], [Entry::SORT_DIRECTION_ASC, Entry::SORT_DIRECTION_DESC])) {
                $sort_direction = Entry::DEFAULT_SORT_DIRECTION;
            } else {
                $sort_direction = $filter_data[self::$FILTER_KEY_SORT][self::$FILTER_KEY_SORT_DIRECTION];
            }
            unset($filter_data[self::$FILTER_KEY_SORT]);
        }

        $filter_validator = Validator::make($filter_data, self::getFilterValidationRules(isset($filter_data[self::$FILTER_KEY_TAGS])));
        if ($filter_validator->fails()) {
            return response([self::$RESPONSE_FILTER_KEY_ERROR => self::$ERROR_MSG_FILTER_INVALID], HttpStatus::HTTP_BAD_REQUEST);
        }

        return $this->provide_paged_entries_response($filter_data, $page_number, $sort_by, $sort_direction);
    }

    /**
     * DELETE /api/entry/{entry_id}
     */
    public function delete_entry(int $entry_id): Response {
        try {
            $entry = Entry::findOrFail($entry_id);
            $entry->delete();
            return response('', HttpStatus::HTTP_NO_CONTENT);
        } catch(\Exception $e) {
            return response('', HttpStatus::HTTP_NOT_FOUND);
        }
    }

    /**
     * POST /api/entry
     */
    public function create_entry(Request $request): Response {
        return $this->modify_entry($request);
    }

    /**
     * PUT /api/entry/{entry_id}
     */
    public function update_entry(int $entry_id, Request $request): Response {
        return $this->modify_entry($request, $entry_id);
    }

    private function modify_entry(Request $request, ?int $updateId = null): Response {
        $request_body = $request->getContent();
        $entry_data = json_decode($request_body, true);

        // no data check
        if (empty($entry_data)) {
            return response(
                [self::$RESPONSE_SAVE_KEY_ID => self::$ERROR_ENTRY_ID, self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        if (is_null($updateId)) {
            $successful_http_status_code = HttpStatus::HTTP_CREATED;
            $required_fields = Entry::get_fields_required_for_creation();

            // missing (required) data check
            $missing_properties = array_diff_key(array_flip($required_fields), $entry_data);
            if (count($missing_properties) > 0) {
                return response(
                    [self::$RESPONSE_SAVE_KEY_ID => self::$ERROR_ENTRY_ID, self::$RESPONSE_SAVE_KEY_ERROR => sprintf(self::$ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode(array_keys($missing_properties)))],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }

            $entry_being_modified = new Entry();
        } else {
            $successful_http_status_code = HttpStatus::HTTP_OK;
            $required_fields = Entry::get_fields_required_for_update();

            try {
                // check to make sure entry exists. if it doesn't then we can't update it
                $entry_being_modified = Entry::findOrFail($updateId);
            } catch (\Exception $e) {
                return response(
                    [self::$RESPONSE_SAVE_KEY_ID => self::$ERROR_ENTRY_ID, self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST],
                    HttpStatus::HTTP_NOT_FOUND
                );
            }
        }

        // check validity of account_type_id value
        if (isset($entry_data['account_type_id'])) {
            if(!$this->checkAccountTypeExists($entry_data['account_type_id'])) {
                return response(
                    [self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::$RESPONSE_SAVE_KEY_ID => self::$ERROR_ENTRY_ID],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        }

        foreach ($entry_data as $property => $value) {
            if (in_array($property, $required_fields)) {
                $entry_being_modified->$property = $value;
            }
        }
        if (isset($entry_data['transfer_entry_id'])) {
            $entry_being_modified->transfer_entry_id = $entry_data['transfer_entry_id'];
        }
        $entry_being_modified->save();

        $entry_tags = !empty($entry_data['tags']) && is_array($entry_data['tags']) ? $entry_data['tags'] : [];
        $this->update_entry_tags($entry_being_modified, $entry_tags);

        $entry_attachments = !empty($entry_data['attachments']) && is_array($entry_data['attachments']) ? $entry_data['attachments'] : [];
        $this->attach_attachments_to_entry($entry_being_modified, $entry_attachments);

        return response(
            [self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_NO_ERROR, self::$RESPONSE_SAVE_KEY_ID => $entry_being_modified->id],
            $successful_http_status_code
        );
    }

    /**
     * POST /api/entry/transfer
     */
    public function create_transfer_entries(Request $request) {
        $request_body = $request->getContent();
        $transfer_data = json_decode($request_body, true);

        if (empty($transfer_data)) {
            return response(
                [self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_NO_DATA, self::$RESPONSE_SAVE_KEY_ID => []],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        // establish what parameters are required in the payload
        $required_transfer_fields = Entry::get_fields_required_for_creation();
        unset(
            $required_transfer_fields[array_search('account_type_id', $required_transfer_fields)],
            $required_transfer_fields[array_search('expense', $required_transfer_fields)],
            $required_transfer_fields[array_search('confirm', $required_transfer_fields)]
        );
        $transfer_specific_fields = [self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE, self::$TRANSFER_KEY_TO_ACCOUNT_TYPE];
        $required_transfer_fields = array_merge($required_transfer_fields, $transfer_specific_fields);

        // missing (required) data check
        $missing_properties = array_diff_key(array_flip($required_transfer_fields), $transfer_data);
        if (count($missing_properties) > 0) {
            return response(
                [self::$RESPONSE_SAVE_KEY_ERROR => sprintf(self::$ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode(array_keys($missing_properties))), self::$RESPONSE_SAVE_KEY_ID => []],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        $entry_tags = !empty($transfer_data['tags']) && is_array($transfer_data['tags']) ? $transfer_data['tags'] : [];

        if (isset($transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE]) && $transfer_data[self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE] != self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID) {
            try {
                $from_entry = $this->initTransferEntry($transfer_data, self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE, $required_transfer_fields, $transfer_specific_fields, $entry_tags);
            } catch(OutOfRangeException $e) {
                return response(
                    [self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::$RESPONSE_SAVE_KEY_ID => []],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        } else {
            $from_entry = null;
        }

        if (isset($transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE]) && $transfer_data[self::$TRANSFER_KEY_TO_ACCOUNT_TYPE] != self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID) {
            try {
                $to_entry = $this->initTransferEntry($transfer_data, self::$TRANSFER_KEY_TO_ACCOUNT_TYPE, $required_transfer_fields, $transfer_specific_fields, $entry_tags);
            } catch(OutOfRangeException $e) {
                return response(
                    [self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::$RESPONSE_SAVE_KEY_ID => []],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        } else {
            $to_entry = null;
        }

        $entry_attachments = !empty($transfer_data['attachments']) && is_array($transfer_data['attachments']) ? $transfer_data['attachments'] : [];
        if (!is_null($to_entry) && !is_null($from_entry)) {
            $to_entry->transfer_entry_id = $from_entry->id;
            $to_entry->save();
            $from_entry->transfer_entry_id = $to_entry->id;
            $from_entry->save();

            // clone the attachments so they can be used in multiple entries
            $new_entry_attachments = [];
            $cloned_entry_attachments = [];
            foreach ($entry_attachments as $entry_attachment) {
                if (!is_array($entry_attachment)) {
                    continue;
                }
                $existing_attachment = Attachment::find($entry_attachment['uuid']);
                if (is_null($existing_attachment)) {
                    $new_attachment = new Attachment();
                    $new_attachment->uuid = $entry_attachment['uuid'];
                    $new_attachment->name = $entry_attachment['name'];
                    if ($new_attachment->storage_exists(true)) {
                        $tmp_file_path = Storage::path($new_attachment->get_tmp_file_path());
                        $clone_attachment = new Attachment();
                        $clone_attachment->uuid = Uuid::uuid4();
                        $clone_attachment->name = $entry_attachment['name'];
                        $clone_attachment->storage_store(file_get_contents($tmp_file_path), true);
                        $cloned_entry_attachments[] = ['uuid' => $clone_attachment->uuid, 'name' => $clone_attachment->name];
                        $new_entry_attachments[] = ['uuid' => $new_attachment->uuid, 'name' => $new_attachment->name];
                    }
                }
            }
            // save attachments to entries
            $this->attach_attachments_to_entry($to_entry, $new_entry_attachments);
            $this->attach_attachments_to_entry($from_entry, $cloned_entry_attachments);

            return response(
                [self::$RESPONSE_SAVE_KEY_ID => [$to_entry->id, $from_entry->id], self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_NO_ERROR],
                HttpStatus::HTTP_CREATED
            );
        } elseif (is_null($to_entry) && !is_null($from_entry)) {
            // "TO" entry is EXTERNAL
            $from_entry->transfer_entry_id = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $from_entry->save();
            $this->attach_attachments_to_entry($from_entry, $entry_attachments);
            return response(
                [self::$RESPONSE_SAVE_KEY_ID => [$from_entry->id], self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_NO_ERROR],
                HttpStatus::HTTP_CREATED
            );
        } elseif (!is_null($to_entry) && is_null($from_entry)) {
            // "FROM" entry is EXTERNAL
            $to_entry->transfer_entry_id = self::$TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $to_entry->save();
            $this->attach_attachments_to_entry($to_entry, $entry_attachments);
            return response(
                [self::$RESPONSE_SAVE_KEY_ID => [$to_entry->id], self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_ENTRY_NO_ERROR],
                HttpStatus::HTTP_CREATED
            );
        } else {
            // "FROM" & "TO" entries are EXTERNAL
            return response(
                [self::$RESPONSE_SAVE_KEY_ID => [], self::$RESPONSE_SAVE_KEY_ERROR => self::$ERROR_MSG_SAVE_TRANSFER_BOTH_EXTERNAL],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @param array $transfer_data
     * @param string $transfer_side
     * @param array $required_transfer_fields
     * @param array $transfer_specific_fields
     * @param array $transfer_entry_tags
     * @return Entry
     *
     * @throws OutOfRangeException
     */
    private function initTransferEntry($transfer_data, string $transfer_side, $required_transfer_fields, $transfer_specific_fields, $transfer_entry_tags): Entry {
        if(!$this->checkAccountTypeExists($transfer_data[$transfer_side])) {
            throw new OutOfRangeException(self::$ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE);
        }

        $transfer_entry = new Entry();
        foreach ($transfer_data as $property => $value) {
            if (in_array($property, $required_transfer_fields)) {
                if (in_array($property, $transfer_specific_fields)) {
                    if ($property == $transfer_side) {
                        $property = 'account_type_id';
                        if ($transfer_side == self::$TRANSFER_KEY_FROM_ACCOUNT_TYPE) {
                            $transfer_entry->expense = 1;
                        } elseif ($transfer_side == self::$TRANSFER_KEY_TO_ACCOUNT_TYPE) {
                            $transfer_entry->expense = 0;
                        }
                    } else {
                        continue;  // skip to next property
                    }
                }
                $transfer_entry->$property = $value;
            }
        }

        $transfer_entry->save();
        $this->update_entry_tags($transfer_entry, $transfer_entry_tags);

        return $transfer_entry;
    }

    private function checkAccountTypeExists(int $accountTypeId): bool {
        try {
            AccountType::withTrashed()->findOrFail($accountTypeId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Entry $entry
     * @param int[] $new_entry_tags
     */
    private function update_entry_tags(Entry $entry, $new_entry_tags) {
        $currently_attached_tags = $entry->tagIds;
        foreach ($new_entry_tags as $new_tag) {
            if (!in_array($new_tag, $currently_attached_tags)) {
                $entry->tags()->attach($new_tag);
            }
        }
        $tags_to_remove = array_diff($currently_attached_tags, $new_entry_tags);
        foreach ($tags_to_remove as $tag_to_remove) {
            $entry->tags()->detach($tag_to_remove);
        }
    }

    private function attach_attachments_to_entry(Entry $entry, array $entry_attachments) {
        foreach ($entry_attachments as $attachment_data) {
            if (!is_array($attachment_data)) {
                continue;
            }

            $existing_attachment = Attachment::find($attachment_data['uuid']);
            if (is_null($existing_attachment)) {
                $new_attachment = new Attachment();
                $new_attachment->uuid = $attachment_data['uuid'];
                $new_attachment->name = $attachment_data['name'];
                $new_attachment->entry_id = $entry->id;
                $new_attachment->storage_move_from_tmp_to_main();
                $new_attachment->save();
            }
        }
    }

    private function provide_paged_entries_response(array $filters, int $page_number = 0, string $sort_by = Entry::DEFAULT_SORT_PARAMETER, string $sort_direction = Entry::DEFAULT_SORT_DIRECTION): Response {
        $entries_collection = Entry::get_collection_of_entries(
            $filters,
            self::$MAX_ENTRIES_IN_RESPONSE,
            self::$MAX_ENTRIES_IN_RESPONSE * $page_number,
            $sort_by,
            $sort_direction
        );

        if (is_null($entries_collection) || $entries_collection->isEmpty()) {
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            $entries_collection->transform(function(Entry $entry) {
                $entry->tags = empty($entry->tags) ? [] : array_map('intval', explode(',', $entry->tags));
                $entry->has_attachments = $entry->attachments_exists;
                $entry->is_transfer = !is_null($entry->transfer_entry_id);
                unset($entry->transfer_entry_id, $entry->attachments_exists);
                $entry->makeHidden(['accountType']);
                return $entry;
            });

            $entries_collection = $entries_collection->values();   // the use of values() here allows us to ignore the original keys of the collection after a sort
            $entries_collection->put('count', Entry::count_collection_of_entries($filters));
            return response($entries_collection, HttpStatus::HTTP_OK);
        }
    }

}
