<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $stdout = fopen('php://stdout', 'w');
    $output = array();
    $output[] = "IAM:".get_current_user();
    $output[] = "whoami:";
    exec("whoami", $output);
    $output[] = "id:";
    exec('id', $output);
    $output[] = "pwd:";
    exec("pwd", $output);
    $output[] = "ls -la ..:";
    exec("ls -la ..", $output);
    $output[] = "ls -la ../storage/logs:";
    exec("ls -la ../storage/logs", $output);

    fwrite($stdout, implode("\n", $output)."\n");
    Log::debug(implode("\n", $output)."\n");

    return view('welcome');
})->name('welcome');

Route::get('/vue-mock', function(){
    if(config('app.debug')){
        return view('vue');
    } else {
        abort(404, "page not available on production");
    }
});

Route::get('/home', 'Web\HomeController@display');
Route::get('/attachment/{uuid}', 'Web\AttachmentController@display');
Route::post('/attachment/upload', 'Web\AttachmentController@upload');
Route::delete('/attachment/upload', 'Web\AttachmentController@deleteUpload');