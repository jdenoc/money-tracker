<?php

namespace App\Http\Controllers\Api;

use App\Models\Tag;
use App\Traits\TagResponseKeys;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class TagController extends Controller {

    use TagResponseKeys;

    /**
     * GET /api/tags
     */
    public function get_tags(){
        $tags = Tag::cache()->get('all');
        if(is_null($tags) || $tags->isEmpty()){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            $tags = $tags->toArray();
            $tags['count'] = Tag::cache()->get('count');
            return response($tags, HttpStatus::HTTP_OK);
        }
    }

    /**
     * POST /api/tag
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createTag(Request $request){
        return $this->modifyTag($request);
    }

    /**
     * PUT /api/tag/{tagId}
     * @param Request $request
     * @param int     $tagId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateTag(Request $request, int $tagId){
        return $this->modifyTag($request, $tagId);
    }

    private function modifyTag(Request $request, int $tag_id=null){
        $request_body = $request->getContent();
        $tag_data = json_decode($request_body, true);

        // no data check
        if(empty($tag_data)){
            return response(
                [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        if(is_null($tag_id)){
            $tag_to_modify = new Tag();
            $http_response_status_code = HttpStatus::HTTP_CREATED;
        } else {
            try{
                $tag_to_modify = Tag::findOrFail($tag_id);
            } catch(Exception $exception){
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_DOES_NOT_EXIST],
                    HttpStatus::HTTP_NOT_FOUND
                );
            }
            $http_response_status_code = HttpStatus::HTTP_OK;
        }

        if(empty($tag_data['name'])){
            return response(
                [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        $tag_to_modify->name = $tag_data['name'];

        // no sense saving if nothing was changed
        if($tag_to_modify->isDirty()){    // isDirty() == has changes
            $tag_to_modify->save();
        }

        return response(
            [self::$RESPONSE_KEY_ID=>$tag_to_modify->id, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_ERROR],
            $http_response_status_code
        );
    }

}
