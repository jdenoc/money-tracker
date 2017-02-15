<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

use App\Tag;

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
