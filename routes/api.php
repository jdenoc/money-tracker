<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('tags', 'Api\TagController@get_tags');                           // /api/tags
Route::get('accounts', 'Api\AccountController@get_accounts');               // /api/accounts
Route::get('account/{account_id}', 'Api\AccountController@get_account');   // /api/account/{account_id}