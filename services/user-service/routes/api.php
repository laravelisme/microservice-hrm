<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/users',  function (Request $request) {

    return response()->json([
        'message' => 'Hello, this is the user service API!',
        'timestamp' => now(),
    ]);
});

Route::prefix('/v2')->group(function () {

    //Auth
    Route::prefix('/auth')->group(function () {
        Route::post('login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
        Route::post('refresh', [\App\Http\Controllers\Auth\AuthController::class, 'refresh']);

        Route::prefix('/')->middleware('auth:api')->group(function () {
            Route::post('logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout']);
        });

    });

});
