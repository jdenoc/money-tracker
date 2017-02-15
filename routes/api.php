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

Route::get('tags', 'Api\TagController@get_tags');                           // GET /api/tags
Route::get('accounts', 'Api\AccountController@get_accounts');               // GET /api/accounts
Route::get('account/{account_id}', 'Api\AccountController@get_account');    // GET /api/account/{account_id}
Route::get('entry/{entry_id}', 'Api\EntryController@get_entry');            // GET /api/entry/{entry_id}
Route::get('entries', 'Api\EntryController@get_entries');                   // GET /api/entries