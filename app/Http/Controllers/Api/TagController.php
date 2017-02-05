<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Tag;

class TagController extends Controller {

    /**
     * GET /api/tags
     */
    public function get_tags(){
        $tags = Tag::all();
        if(is_null($tags) || $tags->isEmpty()){
            return response([], 404);
        } else {
            $tags = $tags->toArray();
            $tags['count'] = Tag::count();
            return response($tags);
        }
    }

}
