<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Attachment;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class AttachmentController extends Controller {

    public function delete_attachment($uuid){
        $attachment = Attachment::find($uuid);
        if(empty($attachment)){
            return response('', HttpStatus::HTTP_NOT_FOUND);
        } else {
            $attachment->forceDelete();
            $attachment->storage_delete();
            return response('', HttpStatus::HTTP_NO_CONTENT);
        }
    }

}