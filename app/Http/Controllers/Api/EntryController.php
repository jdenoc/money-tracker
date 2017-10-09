<?php

namespace App\Http\Controllers\Api;

use App\AccountType;
use App\Attachment;
use App\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

use App\Entry;

class EntryController extends Controller {

    const MAX_ENTRIES_IN_RESPONSE = 50;
    const ERROR_ENTRY_ID = 0;
    const RESPONSE_SAVE_KEY_ID = 'id';
    const RESPONSE_SAVE_KEY_ERROR = 'error';
    const ERROR_MSG_SAVE_ENTRY_NO_ERROR = '';
    const ERROR_MSG_SAVE_ENTRY_NO_DATA = "No data provided";
    const ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY = "Missing data: %s";
    const ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE = "Account type provided does not exist";
    const ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST = "Entry does not exist";

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
        $filter_validator = Validator::make($filter_data, self::get_filter_details());
        if($filter_validator->fails()){
            return response(['error'=>'invalid filter provided'], HttpStatus::HTTP_BAD_REQUEST);
        }

        return $this->provide_paged_entries_response($filter_data, $page_number);
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
            $entry->disabled = true;
            $entry->save();
            return response('', HttpStatus::HTTP_NO_CONTENT);
        }
    }

    /**
     * POST /api/entry
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function create_entry(Request $request){
        $post_body = $request->getContent();
        $entry_data = json_decode($post_body, true);

        // no data check
        if(empty($entry_data)){
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID, self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        // missing (required) data check
        $required_fields = Entry::get_fields_required_for_creation();
        $missing_properties = array_diff_key(array_flip($required_fields), $entry_data);
        if(count($missing_properties) > 0){
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID, self::RESPONSE_SAVE_KEY_ERROR=>sprintf(self::ERROR_MSG_SAVE_ENTRY_MISSING_PROPERTY, json_encode(array_keys($missing_properties)))],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        // check validity of account_type value
        $account_type = AccountType::find($entry_data['account_type']);
        if(empty($account_type)){
            return response(
                [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        $required_fields = Entry::get_fields_required_for_creation();
        $entry = new Entry();
        foreach($required_fields as $entry_property){
            $entry->$entry_property = $entry_data[$entry_property];
        }
        $entry->save();

        $this->attach_tags_to_entry($entry, $entry_data);
        $this->attach_attachments_to_entry($entry, $entry_data);

        return response(
            [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_ERROR, self::RESPONSE_SAVE_KEY_ID=>$entry->id],
            HttpStatus::HTTP_CREATED
        );
    }

    /**
     * PUT /api/entry/{entry_id}
     * @param int $entry_id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function update_entry($entry_id, Request $request){
        $put_body = $request->getContent();
        $entry_data = json_decode($put_body, true);

        // no data check
        if(empty($entry_data)){
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID, self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        // check to make sure entry exists. if it doesn't then we can't update it
        $existing_entry = Entry::find($entry_id);
        if(is_null($existing_entry)){
            return response(
                [self::RESPONSE_SAVE_KEY_ID=>self::ERROR_ENTRY_ID, self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_DOES_NOT_EXIST],
                HttpStatus::HTTP_NOT_FOUND
            );
        }

        // check validity of account_type value
        if(isset($entry_data['account_type'])){
            $account_type = AccountType::find($entry_data['account_type']);
            if(empty($account_type)){
                return response(
                    [self::RESPONSE_SAVE_KEY_ERROR => self::ERROR_MSG_SAVE_ENTRY_INVALID_ACCOUNT_TYPE, self::RESPONSE_SAVE_KEY_ID => self::ERROR_ENTRY_ID],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        }

        $required_fields = Entry::get_fields_required_for_update();
        foreach($entry_data as $property=>$value){
            if(in_array($property, $required_fields)){
                $existing_entry->$property = $value;
            }
        }
        $existing_entry->save();

        $this->attach_tags_to_entry($existing_entry, $entry_data);
        $this->attach_attachments_to_entry($existing_entry, $entry_data);

        return response(
            [self::RESPONSE_SAVE_KEY_ERROR=>self::ERROR_MSG_SAVE_ENTRY_NO_ERROR, self::RESPONSE_SAVE_KEY_ID=>$existing_entry->id],
            HttpStatus::HTTP_OK
        );
    }

    public static function get_filter_details(){
        $tags = Tag::all();
        $tag_ids = $tags->pluck('id')->toArray();
        return [
            'start_date'=>'date_format:Y-m-d',
            'end_date'=>'date_format:Y-m-d',
            'account'=>'integer',
            'account_type'=>'integer',
            'tags'=>'array',
            'tags.*'=>'in:'.implode(',', $tag_ids),
            'expense'=>'boolean',
            'attachments'=>'boolean',
            'min_value'=>'numeric',
            'max_value'=>'numeric',
            'unconfirmed'=>'boolean'
        ];
    }

    /**
     * @param Entry $entry
     * @param array $entry_data
     */
    private function attach_tags_to_entry($entry, $entry_data){
        if(!empty($entry_data['tags']) && is_array($entry_data['tags'])){
            foreach($entry_data['tags'] as $tag){
                if(!is_array($entry_data['tags'])){
                    continue;
                }
                $entry->tags()->attach(intval($tag));
            }
        }
    }

    /**
     * @param Entry $entry
     * @param array $entry_data
     */
    private function attach_attachments_to_entry($entry, $entry_data){
        if(!empty($entry_data['attachments']) && is_array($entry_data['attachments'])){
            foreach($entry_data['attachments'] as $attachment_data){
                if(!is_array($attachment_data)){
                    continue;
                }
                $new_attachment = new Attachment();
                $new_attachment->uuid = $attachment_data['uuid'];
                $new_attachment->attachment = $attachment_data['attachment'];
                $new_attachment->entry_id = $entry->id;
                $new_attachment->save();
            }
        }
    }

    /**
     * @param $filters
     * @param int $page_number
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    private function provide_paged_entries_response($filters, $page_number=0){
        $entries_collection = Entry::get_collection_of_non_disabled_entries(
            $filters,
            EntryController::MAX_ENTRIES_IN_RESPONSE,
            EntryController::MAX_ENTRIES_IN_RESPONSE*$page_number
        );

        if(is_null($entries_collection) || $entries_collection->isEmpty()){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            foreach($entries_collection as $entry){
                $entry->has_attachments = $entry->has_attachments();
                $entry->tags = $entry->get_tag_ids();
            }
            $entries_as_array = $entries_collection->toArray();
            $entries_as_array['count'] = Entry::count_non_disabled_entries($filters);
            return response($entries_as_array, HttpStatus::HTTP_OK);
        }
    }

}