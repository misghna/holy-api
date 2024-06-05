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
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PermissionController;

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
Route::get('global_setting1',[CommonController::class,'globalSettings1']);
//Route::resource('languages', LanguageController::class);
//Route::resource('tenants', TenantController::class);


Route::group(['prefix' => 'protected','middleware' => ['auth:sanctum']], function() {
    Route::get('user',[UserController::class,'userDetails']);
    Route::get('logout',[UserController::class,'logout']);
    Route::controller(FileController::class)->group(function(){
        Route::get('file', 'getOne');
        Route::get('file_list', 'getList');
        Route::post('file', 'store');
        Route::delete('file', 'destroy');
    });
    Route::controller(DocumentController::class)->group(function(){
        Route::get('documents', 'index');
        Route::post('documents', 'store');
        Route::put('documents', 'update');
        Route::delete('documents', 'destroy');
    });

    Route::controller(TenantController::class)->group(function(){
        Route::get('tenants', 'index');
        Route::post('tenants', 'store');
        Route::put('tenants', 'update');
        Route::delete('tenants', 'destroy');
    });

    Route::controller(LanguageController::class)->group(function(){
        Route::get('languages', 'index');
        Route::post('languages', 'store');
        Route::put('languages', 'update');

        Route::get('dictionaries', 'getDict');
        Route::get('dictionary', 'geOneDict');
        Route::put('dictionary', 'updateDict');

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

    Route::controller(UserController::class)->group(function(){
        Route::get('user_profile/edit', 'one');
        Route::post('user_profile', 'store');
        Route::put('user_profile', 'update');
        Route::delete('user_profile', 'destroy');
        Route::get('user_profile', 'all');
    }); 

    Route::controller(PermissionController::class)->group(function(){
        Route::post('permissions/grant', 'grantAccess');
        Route::post('permissions/revoke', 'revokeAccess');
        Route::put('permissions/update', 'updateAccessLevel');
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

