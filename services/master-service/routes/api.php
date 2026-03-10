<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v2/master-data', function (Request $request) {
    return response()->json([
        'status' => true,
        'message' => 'Master data',
        'data' => "Ahahaha bisa"
    ]);
});

Route::prefix('/v2')->middleware([\App\Http\Middleware\VerifyJwt::class])->group(function () {

});
