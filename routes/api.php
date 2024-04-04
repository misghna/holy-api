<?php

use App\Http\Controllers\API\ContentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\FileController;

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
});