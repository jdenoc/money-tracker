<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Attachment;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller {

    public function delete_attachment($uuid){
        $attachment = Attachment::find($uuid);
        if(empty($attachment)){
            return response('', Response::HTTP_NOT_FOUND);
        } else {
            $attachment->forceDelete();
            return response('', Response::HTTP_NO_CONTENT);
        }
    }

}