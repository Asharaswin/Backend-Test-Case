<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\BookController;
use App\Http\Controllers\api\v1\TestController;
use App\Http\Controllers\api\v1\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/book')->group(function(){
    Route::get('/list',[BookController::class,'list'])->name('api-list-book');
    Route::post('/borrow',[BookController::class,'borrow'])->name('api-borrow-book');
    Route::get('/borrow/history',[BookController::class,'history'])->name('api-history-borrow-book');
    Route::put('/return',[BookController::class,'return'])->name('api-return-book');
});

Route::prefix('/user')->group(function(){
    Route::get('/list',[UserController::class,'list'])->name('api-list-user');
    Route::get('/list/borrowing',[UserController::class,'borrowing'])->name('api-borrowing-user');
    Route::get('/borrow/history',[UserController::class,'history'])->name('api-history-borrow-user');
});
