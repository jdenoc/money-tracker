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

Route::controller(InstitutionController::class)->group(function() {
    // /api/institutions
    Route::get('institutions', 'get_institutions')->name('institutions');

    // /api/institution/{?}
    Route::prefix('institution/')->name('institution.')->group(function() {
        Route::delete('{institutionId}', 'disableInstitution')->name('delete');
        Route::get('{institution_id}', 'get_institution')->name('get');
        Route::patch('{institutionId}', 'restoreInstitution')->name('patch');
        Route::post('', 'create_institution')->name('post');
        Route::put('{institution_id}', 'update_institution')->name('put');
    });

    // /api/institutes
    Route::get('institutes', 'get_institutions')->name('institutes');

    // /api/institute/{?}
    Route::prefix('institute/')->name('institute.')->group(function() {
        Route::delete('{institutionId}', 'disabledInstitution')->name('delete');
        Route::get('{institution_id}', 'get_institution')->name('get');
        Route::patch('{institutionId}', 'restoreInstitution')->name('patch');
        Route::post('', 'create_institution')->name('post');
        Route::put('{institution_id}', 'update_institution')->name('put');
    });
});

Route::controller(AccountController::class)->group(function() {
    // /api/accounts
    Route::get('accounts', 'get_accounts')->name('accounts');

    // /api/account/{?}
    Route::prefix('account/')->name('account.')->group(function() {
        Route::delete('{accountId}', 'disableAccount')->name('delete');
        Route::get('{account_id}', 'get_account')->name('get');
        Route::patch('{accountId}', 'reactivateAccount')->name('patch');
        Route::post('', 'create_account')->name('post');
        Route::put('{account_id}', 'update_account')->name('put');
    });
});

// GET /api/tags
Route::get('tags', [TagController::class, 'get_tags'])
    ->name('tags');
// POST /api/tag
Route::post('tag', [TagController::class, 'createTag'])
    ->name('tag.post');
// PUT /api/tag/{tag_id}
Route::put('tag/{tag_id}', [TagController::class, 'updateTag'])
    ->name('tag.put');

Route::controller(AccountTypeController::class)->group(function() {
    // /api/account-types/{?}
    Route::prefix('account-types/')->name('account_types.')->group(function() {
        Route::get('', 'list_account_types')->name('get');
        Route::get('types', 'list_account_type_types')->name('types.get');
    });

    // /api/account-type/{?}
    Route::prefix('account-type/')->name('account_type.')->group(function() {
        Route::delete('{account_type_id}', 'disable_account_type')->name('delete');
        Route::get('{account_type_id}', 'get_account_type')->name('get');
        Route::patch('{account_type_id}', 'enable_account_type')->name('patch');
        Route::post('', 'create_account_type')->name('post');
        Route::put('{account_type_id}', 'update_account_type')->name('put');
    });
});

Route::controller(EntryController::class)->group(function() {
    // /api/entries/{?}
    Route::prefix('entries')->name('entries.')->group(function() {
        Route::get('', 'get_paged_entries')->name('get');
        Route::get('{page}', 'get_paged_entries')->name('get.page');
        Route::post('', 'filter_paged_entries')->name('post');
        Route::post('{page}', 'filter_paged_entries')->name('post.page');
    });

    // /api/entry/{?}
    Route::prefix('entry')->name('entry.')->group(function() {
        Route::delete('{entry_id}', 'delete_entry')->name('delete');
        Route::get('{entry_id}', 'get_entry')->name('get');
        Route::post('', 'create_entry')->name('post');
        Route::post('transfer', 'create_transfer_entries')->name('post.transfer');
        Route::put('{entry_id}', 'update_entry')->name('put');
    });
});

// DELETE /api/attachment/{uuid}
Route::delete('attachment/{uuid}', [AttachmentController::class, 'delete_attachment'])
    ->name('attachment.delete');
