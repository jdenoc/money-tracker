<?php

namespace App\Http\Controllers\Api;

use App\AccountType;
use App\Attachment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

use App\Entry;

class EntryController extends Controller {

    const MAX_ENTRIES_IN_RESPONSE = 50;
    const ERROR_ENTRY_ID = 0;
    const ERROR_MSG_CREATE_ENTRY_NO_ERROR = '';
    const ERROR_MSG_CREATE_ENTRY_NO_DATA = "No data provided";
    const ERROR_MSG_CREATE_ENTRY_MISSING_PROPERTY = "Missing data: %s";
    const ERROR_MSG_CREATE_ENTRY_INVALID_ACCOUNT_TYPE = "Account type provided does not exist";

    /**
     * GET /api/entry/{entry_id}
     * @param int $entry_id
     * @return Response
     */
    public function get_entry($entry_id){
        $entry = Entry::get_entry_with_tags_and_attachments($entry_id);
        if(is_null($entry) || empty($entry) || $entry->deleted == 1){
            return response([], Response::HTTP_NOT_FOUND);
        } else {
            // we're not going to show deleted entries,
            // so why bother telling someone that something that isn't deleted
            $entry->makeHidden('deleted');
            $entry->tags->makeHidden('pivot');  // this is an artifact left over from the relationship logic
            $entry->attachments->makeHidden('entry_id');    // we already know the attachment is associated with this entry, no need to repeat that
            return response($entry, Response::HTTP_OK);
        }
    }

    /**
     * GET /api/entries
     * @return Response
     */
    public function get_entries(){
        return $this->get_paged_entries();
    }

    /**
     * GET /api/entries/{page}
     * @param int $page_number
     * @return Response
     */
    public function get_paged_entries($page_number = 0){
        $entries = Entry::get_collection_of_non_deleted_entries()
            ->slice(self::MAX_ENTRIES_IN_RESPONSE*$page_number, self::MAX_ENTRIES_IN_RESPONSE);
        if(is_null($entries) || $entries->isEmpty()){
            return response([], Response::HTTP_NOT_FOUND);
        } else {
            foreach($entries as $entry){
                $entry->has_attachments = $entry->has_attachments();
                $entry->tags = $entry->get_tag_ids();
            }
            $entries_as_array = $entries->toArray();
            $entries_as_array['count'] = Entry::count_non_deleted_entries();
            return response($entries_as_array, Response::HTTP_OK);
        }
    }

    /**
     * DELETE /api/entry/{entry_id}
     * @param int $entry_id
     * @return Response
     */
    public function delete_entry($entry_id){
        $entry = Entry::find($entry_id);
        if(empty($entry)){
            return response('', Response::HTTP_NOT_FOUND);
        } else {
            $entry->deleted = true;
            $entry->save();
            return response('', Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * POST /api/entry
     * @param Request $request
     * @return Response
     */
    public function create_entry(Request $request){
        $post_body = $request->getContent();
        $entry_data = json_decode($post_body, true);

        // no data check
        if(empty($entry_data)){
            return response(
                ['id'=>self::ERROR_ENTRY_ID, 'error'=>self::ERROR_MSG_CREATE_ENTRY_NO_DATA],
                Response::HTTP_BAD_REQUEST
            );
        }

        // missing (required) data check
        $required_fields = Entry::get_fields_required_for_creation();
        $missing_properties = array_diff_key(array_flip($required_fields), $entry_data);
        if(count($missing_properties) > 0){
            return response(
                ['id'=>self::ERROR_ENTRY_ID, 'error'=>sprintf(self::ERROR_MSG_CREATE_ENTRY_MISSING_PROPERTY, json_encode(array_keys($missing_properties)))],
                Response::HTTP_BAD_REQUEST
            );
        }

        // check validity of account_type value
        $account_type = AccountType::find($entry_data['account_type']);
        if(empty($account_type)){
            return response(
                ['error'=>self::ERROR_MSG_CREATE_ENTRY_INVALID_ACCOUNT_TYPE, 'id'=>self::ERROR_ENTRY_ID],
                Response::HTTP_BAD_REQUEST
            );
        }

        $entry = new Entry();
        foreach($required_fields as $entry_property){
            $entry->$entry_property = $entry_data[$entry_property];
        }
        $entry->save();

        // assign tags
        if(!empty($entry_data['tags']) && is_array($entry_data['tags'])){
            foreach($entry_data['tags'] as $tag){
                $entry->tags()->attach(intval($tag));
            }
        }

        // assign attachments
        if(!empty($entry_data['attachments']) && is_array($entry_data['attachments'])){
            foreach($entry_data['attachments'] as $attachment_data){
                $new_attachment = new Attachment();
                $new_attachment->uuid = $attachment_data['uuid'];
                $new_attachment->attachment = $attachment_data['attachment'];
                $new_attachment->entry_id = $entry->id;
                $new_attachment->save();
            }
        }

        return response(
            ['error'=>self::ERROR_MSG_CREATE_ENTRY_NO_ERROR, 'id'=>$entry->id],
            Response::HTTP_CREATED
        );
    }

}