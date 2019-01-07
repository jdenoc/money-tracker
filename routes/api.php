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

// GET /api/version
Route::get('version', 'Api\VersionController@get')
    ->name('version');
// GET /api/institutions
Route::get('institutions', 'Api\InstitutionController@get_institutions')
    ->name('institutions');
// GET /api/institutes
Route::get('institutes', 'Api\InstitutionController@get_institutions')
    ->name('institutes');
// GET /api/institution/{institution_id}
Route::get('institution/{institution_id}', 'Api\InstitutionController@get_institution')
    ->name('institution');
// GET /api/institute/{institution_id}
Route::get('institute/{institution_id}', 'Api\InstitutionController@get_institution')
    ->name('institute');
// GET /api/tags
Route::get('tags', 'Api\TagController@get_tags')
    ->name('tags');
// GET /api/accounts
Route::get('accounts', 'Api\AccountController@get_accounts')
    ->name('accounts');
// GET /api/account/{account_id}
Route::get('account/{account_id}', 'Api\AccountController@get_account')
    ->name('account');
// GET /api/account-types
Route::get('account-types', 'Api\AccountTypeController@list_account_types')
    ->name('account_types.get');
// DELETE /api/account-type/{account_type_id}
Route::delete('account-type/{account_type_id}', 'Api\AccountTypeController@disable_account_type')
    ->name('account_type.delete');
// GET /api/entries
Route::get('entries', 'Api\EntryController@get_paged_entries')
    ->name('entries.get');
// GET /api/entries/{page}
Route::get('entries/{page}', 'Api\EntryController@get_paged_entries')
    ->name('entries.get.paged');
// POST /api/entries
Route::post('entries', 'Api\EntryController@filter_paged_entries')
    ->name('entries.post');
// POST /api/entries/{page}
Route::post('entries/{page}', 'Api\EntryController@filter_paged_entries')
    ->name('entries.post.paged');
// POST /api/entry
Route::post('entry', 'Api\EntryController@create_entry')
    ->name('entry.post');
// GET /api/entry/{entry_id}
Route::get('entry/{entry_id}', 'Api\EntryController@get_entry')
    ->name('entry.get');
// PUT /api/entry/{entry_id}
Route::put('entry/{entry_id}', 'Api\EntryController@update_entry')
    ->name('entry.put');
// DELETE /api/entry/{entry_id}
Route::delete('entry/{entry_id}', 'Api\EntryController@delete_entry')
    ->name('entry.delete');
// POST /api/entry/transfer
Route::post('entry/transfer', 'Api\EntryController@create_transfer_entries')
    ->name('entry.transfer');
// DELETE /api/attachment/{uuid}
Route::delete('attachment/{uuid}', 'Api\AttachmentController@delete_attachment')
    ->name('attachment.delete');
