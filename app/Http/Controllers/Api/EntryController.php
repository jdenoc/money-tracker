<?php

namespace App\Http\Controllers\Api;

use App\Entry;
use App\AccountType;
use App\Attachment;
use App\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class EntryController extends Controller {

    const MAX_ENTRIES_IN_RESPONSE = 50;
    const ERROR_ENTRY_ID = 0;
    const RESPONSE_SAVE_KEY_ID = 'id';
    const RESPONSE_SAVE_KEY_ERROR = 'error';
    const RESPONSE_FILTER_KEY_ERROR = 'error';
    const ERROR_MSG_SAVE_ENTRY_NO_ERROR = '';
    const ERROR_MSG_SAVE_ENTRY_NO_DATA = "No data provided";
    const ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY = "Missing data: %s";
    const ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE = "Account type provided does not exist";
    const ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST = "Entry does not exist";
    const ERROR_MSG_SAVE_TRANSFER_BOTH_EXTERNAL = "A transfer can not consist with both entries belonging to external accounts";
    const ERROR_MSG_FILTER_INVALID = 'invalid filter provided';
    const FILTER_KEY_ACCOUNT = 'account';
    const FILTER_KEY_ACCOUNT_TYPE = 'account_type';
    const FILTER_KEY_ATTACHMENTS = 'attachments';
    const FILTER_KEY_END_DATE = 'end_date';
    const FILTER_KEY_EXPENSE = 'expense';
    const FILTER_KEY_IS_TRANSFER = 'is_transfer';
    const FILTER_KEY_MAX_VALUE = 'max_value';
    const FILTER_KEY_MIN_VALUE = 'min_value';
    const FILTER_KEY_START_DATE = 'start_date';
    const FILTER_KEY_TAGS = 'tags';
    const FILTER_KEY_UNCONFIRMED = 'unconfirmed';
    const FILTER_KEY_SORT = 'sort';
    const FILTER_KEY_SORT_PARAMETER = 'parameter';
    const FILTER_KEY_SORT_DIRECTION = 'direction';
    const TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID = 0;
    const TRANSFER_KEY_FROM_ACCOUNT_TYPE = 'from_account_type_id';
    const TRANSFER_KEY_TO_ACCOUNT_TYPE = 'to_account_type_id';

    /**
     * GET /api/entry/{entry_id}
     * @param int $entry_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function get_entry($entry_id){
        $entry = Entry::get_entry_with_tags_and_attachments($entry_id);
        if(is_null($entry) || empty($entry) || $entry->disabled == 1){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            // we're not going to show disabled entries,
            // so why bother telling someone that something that isn't disabled
            $entry->makeHidden(['disabled', 'disabled_stamp']);
            $entry->tags->makeHidden('pivot');  // this is an artifact left over from the relationship logic
            $entry->attachments->makeHidden('entry_id');    // we already know the attachment is associated with this entry, no need to repeat that
            return response($entry, HttpStatus::HTTP_OK);
        }
    }

    /**
     * GET /api/entries/{page}
     * @param int $page_number
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function get_paged_entries($page_number = 0){
        return $this->provide_paged_entries_response([], $page_number);
    }

    /**
     * POST /api/entries/{page}
     * @param int $page_number
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function filter_paged_entries(Request $request, $page_number = 0){
        $post_body = $request->getContent();
        $filter_data = json_decode($post_body, true);

        if(empty($filter_data)){
            return $this->provide_paged_entries_response([], $page_number);
        }

        if(empty($filter_data[self::FILTER_KEY_SORT]) || !is_array($filter_data[self::FILTER_KEY_SORT])){
            $sort_by = Entry::DEFAULT_SORT_PARAMETER;
            $sort_direction = Entry::DEFAULT_SORT_DIRECTION;
        } else {
            $sort_by = empty($filter_data[self::FILTER_KEY_SORT][self::FILTER_KEY_SORT_PARAMETER]) ? Entry::DEFAULT_SORT_PARAMETER : $filter_data[self::FILTER_KEY_SORT][self::FILTER_KEY_SORT_PARAMETER];
            if(empty($filter_data[self::FILTER_KEY_SORT][self::FILTER_KEY_SORT_DIRECTION]) || !in_array($filter_data[self::FILTER_KEY_SORT][self::FILTER_KEY_SORT_DIRECTION], [Entry::SORT_DIRECTION_ASC, Entry::SORT_DIRECTION_DESC])){
                $sort_direction = Entry::DEFAULT_SORT_DIRECTION;
            } else {
                $sort_direction = $filter_data[self::FILTER_KEY_SORT][self::FILTER_KEY_SORT_DIRECTION];
            }
            unset($filter_data[self::FILTER_KEY_SORT]);
        }

        $filter_validator = Validator::make($filter_data, self::get_filter_details(isset($filter_data[self::FILTER_KEY_TAGS])));
        if($filter_validator->fails()){
            return response([self::RESPONSE_FILTER_KEY_ERROR=>self::ERROR_MSG_FILTER_INVALID], HttpStatus::HTTP_BAD_REQUEST);
        }

        return $this->provide_paged_entries_response($filter_data, $page_number, $sort_by, $sort_direction);
    }

    /**
     * DELETE /api/entry/{entry_id}
     * @param int $entry_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function delete_entry($entry_id){
        $entry = Entry::find($entry_id);
        if(empty($entry)){
            return response('', HttpStatus::HTTP_NOT_FOUND);
        } else {
            $entry->disable();
            return response('', HttpStatus::HTTP_NO_CONTENT);
        }
    }

    /**
     * POST /api/entry
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function create_entry(Request $request){
        return $this->modify_entry($request);
    }

    /**
     * PUT /api/entry/{entry_id}
     * @param int $entry_id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function update_entry($entry_id, Request $request){
        return $this->modify_entry($request, $entry_id);
    }

    /**
     * @param Request $request
     * @param int|false $update_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    private function modify_entry(Request $request, $update_id=false){
        $request_body = $request->getContent();
        $entry_data = json_decode($request_body, true);

        // no data check
        if(empty($entry_data)){
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID, self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        if($update_id === false){
            $successful_http_status_code = HttpStatus::HTTP_CREATED;
            $required_fields = Entry::get_fields_required_for_creation();

            // missing (required) data check
            $missing_properties = array_diff_key(array_flip($required_fields), $entry_data);
            if(count($missing_properties) > 0){
                return response(
                    [self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID, self::RESPONSE_SAVE_KEY_ERROR=>sprintf(self::ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode(array_keys($missing_properties)))],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }

            $entry_being_modified = new Entry();
        } else {
            $successful_http_status_code = HttpStatus::HTTP_OK;
            $required_fields = Entry::get_fields_required_for_update();

            // check to make sure entry exists. if it doesn't then we can't update it
            $entry_being_modified = Entry::find($update_id);
            if(is_null($entry_being_modified)){
                return response(
                    [self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID, self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST],
                    HttpStatus::HTTP_NOT_FOUND
                );
            }
        }

        // check validity of account_type_id value
        if(isset($entry_data['account_type_id'])){
            $account_type = AccountType::find($entry_data['account_type_id']);
            if(empty($account_type)){
                return response(
                    [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        }

        foreach($entry_data as $property=>$value){
            if(in_array($property, $required_fields)){
                $entry_being_modified->$property = $value;
            }
        }
        if(isset($entry_data['transfer_entry_id'])){
            $entry_being_modified->transfer_entry_id = $entry_data['transfer_entry_id'];
        }
        $entry_being_modified->save();

        $entry_tags = !empty($entry_data['tags']) && is_array($entry_data['tags']) ? $entry_data['tags'] : [];
        $this->update_entry_tags($entry_being_modified, $entry_tags);

        $entry_attachments = !empty($entry_data['attachments']) && is_array($entry_data['attachments']) ? $entry_data['attachments'] : [];
        $this->attach_attachments_to_entry($entry_being_modified, $entry_attachments);

        return response(
            [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_ERROR, self::RESPONSE_SAVE_KEY_ID=>$entry_being_modified->id],
            $successful_http_status_code
        );
    }

    // POST /api/entry/transfer
    public function create_transfer_entries(Request $request){
        $request_body = $request->getContent();
        $transfer_data = json_decode($request_body, true);

        if(empty($transfer_data)){
            return response(
                [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_DATA, self::RESPONSE_SAVE_KEY_ID=>[]],
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
        $transfer_specific_fields = [self::TRANSFER_KEY_FROM_ACCOUNT_TYPE, self::TRANSFER_KEY_TO_ACCOUNT_TYPE];
        $required_transfer_fields = array_merge($required_transfer_fields, $transfer_specific_fields);

        // missing (required) data check
        $missing_properties = array_diff_key(array_flip($required_transfer_fields), $transfer_data);
        if(count($missing_properties) > 0){
            return response(
                [self::RESPONSE_SAVE_KEY_ERROR=>sprintf(self::ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode(array_keys($missing_properties))), self::RESPONSE_SAVE_KEY_ID=>[]],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        $from_entry = null;
        if(isset($transfer_data[self::TRANSFER_KEY_FROM_ACCOUNT_TYPE]) && $transfer_data[self::TRANSFER_KEY_FROM_ACCOUNT_TYPE] != self::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID){
            // check validity of account_type_id value
            $account_type = AccountType::find($transfer_data[self::TRANSFER_KEY_FROM_ACCOUNT_TYPE]);
            if(empty($account_type)){
                return response(
                    [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::RESPONSE_SAVE_KEY_ID=>[]],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }

            $from_entry = new Entry();
            foreach($transfer_data as $property=>$value){
                if(in_array($property, $required_transfer_fields)){
                    if(in_array($property, $transfer_specific_fields)){
                        if($property == self::TRANSFER_KEY_FROM_ACCOUNT_TYPE){
                            $property = 'account_type_id';
                        } else {
                            continue;
                        }
                    }
                    $from_entry->$property = $value;
                }
            }
            $from_entry->expense = 1;

        }

        $to_entry = null;
        if(isset($transfer_data[self::TRANSFER_KEY_TO_ACCOUNT_TYPE]) && $transfer_data[self::TRANSFER_KEY_TO_ACCOUNT_TYPE] != self::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID){
            // check validity of account_type_id value
            $account_type = AccountType::find($transfer_data[self::TRANSFER_KEY_TO_ACCOUNT_TYPE]);
            if(empty($account_type)){
                return response(
                    [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::RESPONSE_SAVE_KEY_ID=>[]],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }

            $to_entry = new Entry();
            foreach($transfer_data as $property=>$value){
                if(in_array($property, $required_transfer_fields)){
                    if(in_array($property, $transfer_specific_fields)){
                        if($property == self::TRANSFER_KEY_TO_ACCOUNT_TYPE){
                            $property = 'account_type_id';
                        } else {
                            continue;
                        }
                    }
                    $to_entry->$property = $value;
                }
            }
            $to_entry->expense = 0;
        }

        $entry_tags = !empty($transfer_data['tags']) && is_array($transfer_data['tags']) ? $transfer_data['tags'] : [];

        if(!is_null($to_entry)){
            $to_entry->save();
            $this->update_entry_tags($to_entry, $entry_tags);
        }

        if(!is_null($from_entry)){
            $from_entry->save();
            $this->update_entry_tags($from_entry, $entry_tags);
        }

        $entry_attachments = !empty($transfer_data['attachments']) && is_array($transfer_data['attachments']) ? $transfer_data['attachments'] : [];
        if(!is_null($to_entry) && !is_null($from_entry)){
            $to_entry->transfer_entry_id = $from_entry->id;
            $to_entry->save();
            $from_entry->transfer_entry_id = $to_entry->id;
            $from_entry->save();

            // clone the attachments so they can be used in multiple entries
            $new_entry_attachments = [];
            $cloned_entry_attachments = [];
            foreach($entry_attachments as $entry_attachment){
                if(!is_array($entry_attachment)){
                    continue;
                }
                $existing_attachment = Attachment::find($entry_attachment['uuid']);
                if(is_null($existing_attachment)){
                    $new_attachment = new Attachment();
                    $new_attachment->uuid = $entry_attachment['uuid'];
                    $new_attachment->name = $entry_attachment['name'];
                    if($new_attachment->storage_exists(true)){
                        $tmp_file_path = Storage::path($new_attachment->get_tmp_file_path());
                        $clone_attachment = new Attachment();
                        $clone_attachment->uuid = Uuid::uuid4();
                        $clone_attachment->name = $entry_attachment['name'];
                        $clone_attachment->storage_store(file_get_contents($tmp_file_path), true);
                        $cloned_entry_attachments[] = ['uuid'=>$clone_attachment->uuid, 'name'=>$clone_attachment->name];
                        $new_entry_attachments[] = ['uuid'=>$new_attachment->uuid, 'name'=>$new_attachment->name];
                    }
                }
            }
            // save attachments to entries
            $this->attach_attachments_to_entry($to_entry, $new_entry_attachments);
            $this->attach_attachments_to_entry($from_entry, $cloned_entry_attachments);

            return response(
                [self::RESPONSE_SAVE_KEY_ID=>[$to_entry->id, $from_entry->id], self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_ERROR],
                HttpStatus::HTTP_CREATED
            );
        } elseif(is_null($to_entry) && !is_null($from_entry)){
            // "TO" entry is EXTERNAL
            $from_entry->transfer_entry_id = self::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $from_entry->save();
            $this->attach_attachments_to_entry($from_entry, $entry_attachments);
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>[$from_entry->id], self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_ERROR],
                HttpStatus::HTTP_CREATED
            );
        } elseif(!is_null($to_entry) && is_null($from_entry)){
            // "FROM" entry is EXTERNAL
            $to_entry->transfer_entry_id = self::TRANSFER_EXTERNAL_ACCOUNT_TYPE_ID;
            $to_entry->save();
            $this->attach_attachments_to_entry($to_entry, $entry_attachments);
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>[$to_entry->id], self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_ERROR],
                HttpStatus::HTTP_CREATED
            );
        } else {
            // "FROM" & "TO" entries are EXTERNAL
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>[], self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_TRANSFER_BOTH_EXTERNAL],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @param bool $include_tag_ids
     * @return array
     */
    public static function get_filter_details($include_tag_ids = true){
        $filter_details = [
            self::FILTER_KEY_START_DATE=>'date_format:Y-m-d',
            self::FILTER_KEY_END_DATE=>'date_format:Y-m-d',
            self::FILTER_KEY_ACCOUNT=>'integer',
            self::FILTER_KEY_ACCOUNT_TYPE=>'integer',
            self::FILTER_KEY_TAGS=>'array',
            self::FILTER_KEY_EXPENSE=>'boolean',
            self::FILTER_KEY_ATTACHMENTS=>'boolean',
            self::FILTER_KEY_MIN_VALUE=>'numeric',
            self::FILTER_KEY_MAX_VALUE=>'numeric',
            self::FILTER_KEY_UNCONFIRMED=>'boolean',
            self::FILTER_KEY_IS_TRANSFER=>'boolean'
        ];

        if($include_tag_ids){
            $tags = Tag::all();
            $tag_ids = $tags->pluck('id')->toArray();
            $filter_details['tags.*'] = 'in:'.implode(',', $tag_ids);
        }

        return $filter_details;
    }

    /**
     * @param Entry $entry
     * @param int[] $new_entry_tags
     */
    private function update_entry_tags($entry, $new_entry_tags){
        $currently_attached_tags = $entry->get_tag_ids();
        foreach($new_entry_tags as $new_tag){
            if(!in_array($new_tag, $currently_attached_tags)){
                $entry->tags()->attach(intval($new_tag));
            }
        }
        $tags_to_remove = array_diff($currently_attached_tags, $new_entry_tags);
        foreach($tags_to_remove as $tag_to_remove){
            $entry->tags()->detach($tag_to_remove);
        }
    }

    /**
     * @param Entry $entry
     * @param array $entry_attachments
     */
    private function attach_attachments_to_entry($entry, $entry_attachments){
        foreach($entry_attachments as $attachment_data){
            if(!is_array($attachment_data)){
                continue;
            }

            $existing_attachment = Attachment::find($attachment_data['uuid']);
            if(is_null($existing_attachment)){
                $new_attachment = new Attachment();
                $new_attachment->uuid = $attachment_data['uuid'];
                $new_attachment->name = $attachment_data['name'];
                $new_attachment->entry_id = $entry->id;
                $new_attachment->storage_move_from_tmp_to_main();
                $new_attachment->save();
            }
        }
    }

    /**
     * @param array $filters
     * @param int $page_number
     * @param string $sort_by
     * @param string $sort_direction
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    private function provide_paged_entries_response($filters, $page_number=0, $sort_by=Entry::DEFAULT_SORT_PARAMETER, $sort_direction=Entry::DEFAULT_SORT_DIRECTION){
        $entries_collection = Entry::get_collection_of_non_disabled_entries(
            $filters,
            EntryController::MAX_ENTRIES_IN_RESPONSE,
            EntryController::MAX_ENTRIES_IN_RESPONSE*$page_number,
            $sort_by,
            $sort_direction
        );

        if(is_null($entries_collection) || $entries_collection->isEmpty()){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            foreach($entries_collection as $entry){
                $entry->has_attachments = $entry->has_attachments();
                $entry->tags = ($entry->has_tags()) ? $entry->tags = $entry->get_tag_ids() : [];
                $entry->is_transfer = !is_null($entry->transfer_entry_id);
                unset($entry->transfer_entry_id);
            }
            $entries_collection = $entries_collection->values();   // the use of values() here allows us to ignore the original keys of the collection after a sort
            $entries_collection->put('count', Entry::count_non_disabled_entries($filters));
            return response($entries_collection, HttpStatus::HTTP_OK);
        }
    }

}