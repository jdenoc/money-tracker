<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

use App\Entry;

class EntryController extends Controller {

    const MAX_ENTRIES_IN_RESPONSE = 50;

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

}