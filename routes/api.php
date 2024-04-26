<?php

use App\Http\Controllers\API\ContentController;
use App\Http\Controllers\API\DocumentController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\PageConfigController;
use App\Http\Controllers\API\ContentConfigController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\LanguageController;

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
Route::post('refresh-token', [UserController::class, 'refreshToken']);
Route::get('contents',[ContentController::class,'all']);
Route::get('content',[ContentController::class,'one']);
Route::get('global_setting',[CommonController::class,'globalSettings']);
Route::resource('languages', LanguageController::class);

Route::group(['prefix' => 'secured','middleware' => ['auth:sanctum']], function() {
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
    Route::controller(PageConfigController::class)->group(function(){
        Route::get('page_configs', 'all');
        Route::get('page_config', 'one');
        Route::post('page_config', 'store');
        Route::put('page_config', 'update');
        Route::delete('page_config', 'destroy');
    });
    Route::controller(ContentController::class)->group(function(){
        Route::post('content', 'store');
        Route::put('content', 'update');
        Route::delete('content', 'destroy');
    });
});

// Clear application cache:
Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return 'Application cache has been cleared';
});

//Clear route cache:
Route::get('/route-cache', function() {
Artisan::call('route:cache');
    return 'Routes cache has been cleared';
});

//Clear config cache:
Route::get('/config-cache', function() {
  Artisan::call('config:cache');
  return 'Config cache has been cleared';
}); 

// Clear view cache:
Route::get('/view-clear', function() {
    Artisan::call('view:clear');
    return 'View cache has been cleared';
});

