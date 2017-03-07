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

Route::get('tags', 'Api\TagController@get_tags');                                   // GET /api/tags
Route::get('accounts', 'Api\AccountController@get_accounts');                       // GET /api/accounts
Route::get('account/{account_id}', 'Api\AccountController@get_account');            // GET /api/account/{account_id}
Route::get('entry/{entry_id}', 'Api\EntryController@get_entry');                    // GET /api/entry/{entry_id}
Route::get('entries', 'Api\EntryController@get_paged_entries');                     // GET /api/entries
Route::get('entries/{page}', 'Api\EntryController@get_paged_entries');              // GET /api/entries/{page}
Route::delete('attachment/{uuid}', 'Api\AttachmentController@delete_attachment');   // DELETE /api/attachment/{uuid}
Route::delete('entry/{entry_id}', 'Api\EntryController@delete_entry');              // DELETE /api/entry/{entry_id}
Route::post('entry', 'Api\EntryController@create_entry');                           // POST /api/entry
Route::put('entry/{entry_id}', 'Api\EntryController@update_entry');                 // PUT /api/entry/{entry_id}
Route::post('entries', 'Api\EntryController@filter_paged_entries');
Route::post('entries/{page}', 'Api\EntryController@filter_paged_entries');