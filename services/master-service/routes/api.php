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

    Route::prefix('company')->group(function () {
        Route::get('/', [\App\Http\Controllers\Company\CompanyController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Company\CompanyController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Company\CompanyController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Company\CompanyController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Company\CompanyController::class, 'destroy']);
    });

    Route::prefix('department')->group(function () {
        Route::get('/', [\App\Http\Controllers\Department\DepartmentController::class, 'index']);
        Route::get('/options', [\App\Http\Controllers\Department\DepartmentController::class, 'companyOptions']);
        Route::post('/', [\App\Http\Controllers\Department\DepartmentController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Department\DepartmentController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Department\DepartmentController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Department\DepartmentController::class, 'destroy']);
    });

    Route::prefix('lokasi-kerja')->group(function () {
        Route::get('/', [\App\Http\Controllers\LokasiKerja\LokasiKerjaController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\LokasiKerja\LokasiKerjaController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\LokasiKerja\LokasiKerjaController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\LokasiKerja\LokasiKerjaController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\LokasiKerja\LokasiKerjaController::class, 'destroy']);
    });

    Route::prefix('saldo-cuti')->group(function () {
        Route::get('/', [\App\Http\Controllers\SaldoCuti\SaldoCutiController::class, 'index']);
        Route::get('/jabatan-options', [\App\Http\Controllers\SaldoCuti\SaldoCutiController::class, 'jabatanOptions']);
        Route::get('/jenis-cuti-options', [\App\Http\Controllers\SaldoCuti\SaldoCutiController::class, 'jenisCutiOptions']);
        Route::post('/', [\App\Http\Controllers\SaldoCuti\SaldoCutiController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\SaldoCuti\SaldoCutiController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\SaldoCuti\SaldoCutiController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\SaldoCuti\SaldoCutiController::class, 'destroy']);
    });

    Route::prefix('hari-libur')->group(function () {
        Route::get('/', [\App\Http\Controllers\HariLibur\HariLiburController::class, 'index']);
        Route::get('/company-options', [\App\Http\Controllers\HariLibur\HariLiburController::class, 'companyOptions']);
        Route::post('/', [\App\Http\Controllers\HariLibur\HariLiburController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\HariLibur\HariLiburController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\HariLibur\HariLiburController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\HariLibur\HariLiburController::class, 'destroy']);
    });

});

Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Endpoint tidak ditemukan',
        'code' => 404,
    ], 404);
});
