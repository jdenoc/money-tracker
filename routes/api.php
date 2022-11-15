<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountTypeController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\VersionController;
use Illuminate\Support\Facades\Route;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

// GET /api/version
Route::get('version', [VersionController::class, 'get'])
    ->name('version');

// GET /api/institutions
Route::get('institutions', [InstitutionController::class,'get_institutions'])
    ->name('institutions');
// GET /api/institutes
Route::get('institutes', [InstitutionController::class,'get_institutions'])
    ->name('institutes');
// GET /api/institution/{institution_id}
Route::get('institution/{institution_id}', [InstitutionController::class,'get_institution'])
    ->name('institution');
// GET /api/institute/{institution_id}
Route::get('institute/{institution_id}', [InstitutionController::class,'get_institution'])
    ->name('institute');
Route::put('institution/{institution_id}', [InstitutionController::class,'update_institution'])
    ->name('institution.put');
Route::put('institute/{institution_id}', [InstitutionController::class,'update_institution'])
    ->name('institute.put');
Route::post('institution', [InstitutionController::class,'create_institution'])
    ->name('institution.post');
Route::post('institute', [InstitutionController::class,'create_institution'])
    ->name('institute.post');

// GET /api/tags
Route::get('tags', [TagController::class, 'get_tags'])
    ->name('tags');
// POST /api/tag
Route::post('tag', [TagController::class, 'createTag'])
    ->name('tag.post');
// PUT /api/tag/{tag_id}
Route::put('tag/{tag_id}', [TagController::class, 'updateTag'])
    ->name('tag.put');

// GET /api/accounts
Route::get('accounts', [AccountController::class, 'get_accounts'])
    ->name('accounts');
// DELETE /api/account/{accountId}
Route::delete('account/{accountId}', [AccountController::class, 'disableAccount'])
    ->name('account.delete');
// GET /api/account/{account_id}
Route::get('account/{account_id}', [AccountController::class, 'get_account'])
    ->name('account.get');
// PATCH /api/account/{accountId}
Route::patch('account/{accountId}', [AccountController::class, 'reactivateAccount'])
    ->name('account.patch');
// POST /api/account
Route::post('account', [AccountController::class, 'create_account'])
    ->name('account.post');
// PUT /api/account/{account_id}
Route::put('account/{account_id}', [AccountController::class, 'update_account'])
    ->name('account.put');

// GET /api/account-types
Route::get('account-types', [AccountTypeController::class, 'list_account_types'])
    ->name('account_types.get');
// GET /api/account-types/types
Route::get('account-types/types', [AccountTypeController::class, 'list_account_type_types'])
    ->name('account-type.types.get');
// GET /api/account-type/{account_type_id}
Route::get('account-type/{account_type_id}', [AccountTypeController::class, 'get_account_type'])
    ->name('account_type.get');
// DELETE /api/account-type/{account_type_id}
Route::delete('account-type/{account_type_id}', [AccountTypeController::class, 'disable_account_type'])
    ->name('account_type.delete');
// POST /api/account-type
Route::post('account-type', [AccountTypeController::class, 'create_account_type'])
    ->name('account_type.post');
// PUT /api/account-type/{account_type_id}
Route::put('account-type/{account_type_id}', [AccountTypeController::class, 'update_account_type'])
    ->name('account_type.put');

// GET /api/entries
Route::get('entries', [EntryController::class, 'get_paged_entries'])
    ->name('entries.get');
// GET /api/entries/{page}
Route::get('entries/{page}', [EntryController::class, 'get_paged_entries'])
    ->name('entries.get.paged');
// POST /api/entries
Route::post('entries', [EntryController::class, 'filter_paged_entries'])
    ->name('entries.post');
// POST /api/entries/{page}
Route::post('entries/{page}', [EntryController::class, 'filter_paged_entries'])
    ->name('entries.post.paged');

// POST /api/entry
Route::post('entry', [EntryController::class, 'create_entry'])
    ->name('entry.post');
// GET /api/entry/{entry_id}
Route::get('entry/{entry_id}', [EntryController::class, 'get_entry'])
    ->name('entry.get');
// PUT /api/entry/{entry_id}
Route::put('entry/{entry_id}', [EntryController::class, 'update_entry'])
    ->name('entry.put');
// DELETE /api/entry/{entry_id}
Route::delete('entry/{entry_id}', [EntryController::class, 'delete_entry'])
    ->name('entry.delete');
// POST /api/entry/transfer
Route::post('entry/transfer', [EntryController::class, 'create_transfer_entries'])
    ->name('entry.transfer');

// DELETE /api/attachment/{uuid}
Route::delete('attachment/{uuid}', [AttachmentController::class, 'delete_attachment'])
    ->name('attachment.delete');
