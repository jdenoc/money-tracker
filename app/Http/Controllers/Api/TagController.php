<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller {

    /**
     * GET /api/tags
     */
    public function get_tags(){
        $tags = Tag::all();
        if(is_null($tags) || $tags->isEmpty()){
            return response([], Response::HTTP_NOT_FOUND);
        } else {
            $tags = $tags->toArray();
            $tags['count'] = Tag::count();
            return response($tags, Response::HTTP_OK);
        }
    }

}
