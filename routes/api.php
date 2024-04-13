<?php

use App\Http\Controllers\API\ContentController;
use App\Http\Controllers\API\DocumentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\PageConfigController;
use App\Http\Controllers\API\ContentConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login',[UserController::class,'loginUser']);
Route::get('contents',[ContentController::class,'all']);
Route::get('content',[ContentController::class,'one']);

Route::group(['prefix' => 'secure','middleware' => ['auth:sanctum']], function() {
    Route::get('user',[UserController::class,'userDetails']);
    Route::get('logout',[UserController::class,'logout']);
    Route::controller(FileController::class)->group(function(){
        Route::get('file', 'index');
        Route::post('file', 'store');
        Route::delete('file', 'destroy');
    });
    Route::controller(DocumentController::class)->group(function(){
        Route::get('documents', 'index');
        Route::post('documents', 'store');
        Route::put('documents', 'update');
        Route::delete('documents', 'destroy');
    });

    Route::get('page_configs',[PageConfigController::class,'all']);
    Route::get('page_config',[PageConfigController::class,'one']);
    Route::post('page_config',[PageConfigController::class,'createPageConfig']);
    Route::put('page_config/update',[PageConfigController::class,'update']);
    Route::delete('page_config', [PageConfigController::class,'destroy']);

    Route::get('content_configs',[ContentConfigController::class,'all']);
    Route::get('content_config',[ContentConfigController::class,'one']);
    Route::post('content_config',[ContentConfigController::class,'createContentConfig']);
    Route::put('content_config/update',[ContentConfigController::class,'update']);
    Route::delete('content_config', [ContentConfigController::class,'destroy']);
});
