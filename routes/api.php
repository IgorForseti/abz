<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\Models\User;

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
Route::group(['prefix' => 'v1'],function () {
    Route::get('/users/{id}',[Controllers\UserController::class,'getUser']);
    Route::get('/users',[Controllers\UserController::class,'getUsers']);
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/users', [Controllers\UserController::class, 'store']);
    });
    Route::get('/token',function () {
        $user = User::orderBy('id')->first();
        $token = $user ? $user->createToken('create_one_user')->plainTextToken : null;

        return [
            'success' => (bool)$token,
            'token' => $token
        ];
    });
    Route::get('/positions',[Controllers\PositionController::class,'getPositions']);
});
