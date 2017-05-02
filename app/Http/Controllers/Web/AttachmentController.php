<?php

namespace App\Http\Controllers\Web;

use App\Attachment;
use Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Ramsey\Uuid\Uuid;

class AttachmentController extends Controller {

    const STORAGE_TMP_UPLOAD = 'tmp_uploads';
    const STORAGE_ATTACHMENTS = 'attachments';

    public function display($uuid){
        if(!Uuid::isValid($uuid)){
            abort(HttpStatus::HTTP_NOT_FOUND, 'Attachment not found');
        }

        $attachment = Attachment::find($uuid);
        if(is_null($attachment)){
            abort(HttpStatus::HTTP_NOT_FOUND, 'Attachment not found');
        }

        $storage_filename = self::STORAGE_ATTACHMENTS.DIRECTORY_SEPARATOR.$attachment->get_hashed_filename();
        if(!Storage::exists($storage_filename)){
            abort(HttpStatus::HTTP_NOT_FOUND, 'Attachment not found');
        }

        // display attachment based on file extension
        switch(strtolower($attachment->get_filename_extension())){
            case 'pdf':
                $display_headers = [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$attachment->attachment.'"'
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

            default:
                $display_headers = [];
                abort(HttpStatus::HTTP_UNSUPPORTED_MEDIA_TYPE, 'Could not display attachment');
        }

        return Response::make(Storage::get($storage_filename), HttpStatus::HTTP_OK, $display_headers);
    }

    public function upload(Request $request){
        $upload_file_request = $request->file('attachment');
        if($upload_file_request->isValid()){
            $new_filename = str_replace(' ', '_', $upload_file_request->getClientOriginalName());
            $upload_file_request->storeAs(self::STORAGE_TMP_UPLOAD, $new_filename);
            return response(
                ['uuid'=>Uuid::uuid4(), 'attachment'=>$upload_file_request->getClientOriginalName(), 'tmp_filename'=>$new_filename],
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
        Storage::delete(self::STORAGE_TMP_UPLOAD.DIRECTORY_SEPARATOR.$request->input('filename'));
        return response('', HttpStatus::HTTP_NO_CONTENT);
    }

}