<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(){
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function(TokenMismatchException $e, Request $request){
            // handle TokenMismatchException's for attachment/upload
            if($request->is('attachment/upload')){
                return response()->json(
                    ['error' => "token mis-match"],
                    HttpStatus::HTTP_UNAUTHORIZED
                );
            }
        });

        $this->renderable(function(PostTooLargeException $e, Request $request){
            if($request->is('attachment/upload')){
                return response()->json(
                    ['error'=>'The uploaded file exceeds your post_max_size ini directive.'],
                    HttpStatus::HTTP_REQUEST_ENTITY_TOO_LARGE
                );
            }
        });

        $this->renderable(function(\Exception $e, Request $request){
            if($request->is('attachment/upload')){
                return response()->json(
                    ['error'=>'Error occurred during upload. Contact admin.'],
                    HttpStatus::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        });
    }

}
