<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('/v2/master-data')->middleware([\App\Http\Middleware\VerifyJwt::class])->group(function () {
    Route::prefix('jabatan')->group(function () {
        Route::get('/', [\App\Http\Controllers\Jabatan\JabatanController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Jabatan\JabatanController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Jabatan\JabatanController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Jabatan\JabatanController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Jabatan\JabatanController::class, 'destroy']);
    });
});
