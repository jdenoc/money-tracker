<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use \Symfony\Component\HttpFoundation\Response as HttpStatus;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception){
        if ($exception instanceof TokenMismatchException && $request->is('attachment/upload')){
            // handle TokenMismatchException's for attachment/upload
            return response(['error' => "token mis-match"], HttpStatus::HTTP_UNAUTHORIZED);

        } elseif($exception instanceof PostTooLargeException && $request->is('attachment/upload')){
            return response(
                ['error'=>'The uploaded file exceeds your post_max_size ini directive.'],
                HttpStatus::HTTP_REQUEST_ENTITY_TOO_LARGE
            );
        } elseif($request->is('attachment/upload')){
            return response(
                ['error'=>'Error occurred during upload. Contact admin.'],
                HttpStatus::HTTP_INTERNAL_SERVER_ERROR
            );
        } else {
            return parent::render($request, $exception);
        }
    }

}
