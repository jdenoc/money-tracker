<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Entry;

class EntryController extends Controller {

    /**
     * GET /api/entry/{entry_id}
     * @param int $entry_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function get_entry($entry_id){
        $entry = Entry::with(['tags', 'attachments'])
            ->where('id', $entry_id)
            ->where('deleted', 0)
            ->first();
        if(is_null($entry) || empty($entry)){
            return response([], 404);
        } else {
            // we're not going to show deleted entries,
            // so why bother telling someone that something that isn't deleted
            $entry->makeHidden('deleted');
            $entry->tags->makeHidden('pivot');  // this is an artifact left over from the relationship logic
            $entry->attachments->makeHidden('entry_id');    // we already know the attachment is associated with this entry, no need to repeat that
            \Carbon\Carbon::setToStringFormat('c');
            return response($entry);
        }
    }

}