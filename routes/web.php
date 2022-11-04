<?php

use App\Http\Controllers\Web\AttachmentController;
use App\Http\Controllers\Web\ExportsController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\StatsController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;
use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;

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

Route::get('/laravel', [HomeController::class, 'laravelWelcome'])->name('laravel-welcome');
Route::get('/', [HomeController::class, 'display']);
Route::get('/home', [HomeController::class, 'display']);
Route::get('/stats', StatsController::class);
Route::get('/settings', SettingsController::class);
Route::get('/attachment/{uuid}', [AttachmentController::class, 'display']);
Route::post('/attachment/upload', [AttachmentController::class, 'upload']);
Route::delete('/attachment/upload', [AttachmentController::class, 'deleteUpload']);
Route::post('/export', ExportsController::class);
Route::get('/health', HealthCheckResultsController::class);
Route::get('/health.json', HealthCheckJsonResultsController::class);
