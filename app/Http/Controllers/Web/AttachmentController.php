<?php

namespace App\Http\Controllers\Web;

use App\Attachment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Ramsey\Uuid\Uuid;

class AttachmentController extends Controller {

    public function display($uuid){
        if(!Uuid::isValid($uuid)){
            abort(HttpStatus::HTTP_NOT_FOUND, 'Attachment not found');
        }

        $attachment = Attachment::find($uuid);
        if(is_null($attachment)){
            abort(HttpStatus::HTTP_NOT_FOUND, 'Attachment not found');
        }

        if(!$attachment->storage_exists()){
            abort(HttpStatus::HTTP_NOT_FOUND, 'Attachment not found');  // TODO: build nicer "not found" page
        }

        // display attachment based on file extension
        switch(strtolower($attachment->get_filename_extension())){
            case 'pdf':
                $display_headers = [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$attachment->name.'"'
                ];
                break;

            case 'jpeg':
            case 'jpg':
                $display_headers = ['Content-Type'=>'image/jpeg'];
                break;

            case 'png':
                $display_headers = ['Content-Type'=>'image/png'];
                break;

            case 'gif':
                $display_headers = ['Content-Type'=>'image/gif'];
                break;

            case 'txt':
                $display_headers = ['Content-Type'=>'text/plain'];
                break;

            default:
                $display_headers = [];
                abort(HttpStatus::HTTP_UNSUPPORTED_MEDIA_TYPE, 'Could not display attachment');
        }

        return Response::make(Storage::get($attachment->get_storage_file_path()), HttpStatus::HTTP_OK, $display_headers);
    }

    public function upload(Request $request){
        $upload_file_request = $request->file('attachment');
        if($upload_file_request->isValid()){
            $attachment = new Attachment();
            $attachment->uuid = Uuid::uuid4();
            $attachment->name = $upload_file_request->getClientOriginalName();
            $attachment->storage_store(file_get_contents($upload_file_request->getRealPath()), true);
            return response(
                ['uuid'=>$attachment->uuid, 'name'=>$attachment->name, 'tmp_filename'=>$attachment->get_tmp_filename()],
                HttpStatus::HTTP_OK
            );

        } else {
            $upload_file_request->getError();
            return response(
                ['error'=>$upload_file_request->getErrorMessage()],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }
    }

    public function deleteUpload(Request $request){
        if(empty($request->input('filename')) || empty($request->input('uuid'))){
            return response('', HttpStatus::HTTP_BAD_REQUEST);
        } else {
            $attachment = new Attachment();
            $attachment->uuid = $request->input('uuid');
            $attachment->name = $request->input('filename');
            if($attachment->storage_exists(true)){
                $attachment->storage_delete(true);
                return response('', HttpStatus::HTTP_NO_CONTENT);
            } else {
                return response('', HttpStatus::HTTP_NOT_FOUND);
            }
        }
    }

}