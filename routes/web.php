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

Route::get('/laravel', function () {
    return view('laravel-welcome');
})->name('laravel-welcome');

Route::get('/', 'Web\HomeController@display');
Route::get('/attachment/{uuid}', 'Web\AttachmentController@display');
Route::post('/attachment/upload', 'Web\AttachmentController@upload');
Route::delete('/attachment/upload', 'Web\AttachmentController@deleteUpload');