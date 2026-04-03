<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PresensiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Siswa
    Route::prefix('siswa')->group(function () {
        Route::post('/presensi/scan', [PresensiController::class, 'scan']);
        Route::get('/presensi/history', [PresensiController::class, 'history']);
        Route::get('/presensi/today', [PresensiController::class, 'today']);
    });

    // Guru
    Route::prefix('guru')->group(function () {
        Route::post('/sesi', [PresensiController::class, 'start']);
        Route::get('/sesi/{id}/qr', [PresensiController::class, 'qr']);
        Route::post('/sesi/{id}/refresh', [PresensiController::class, 'refreshQr']);
        Route::get('/sesi/{id}/monitoring', [PresensiController::class, 'monitoring']);
        Route::post('/sesi/{id}/close', [PresensiController::class, 'close']);
    });

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/laporan', [PresensiController::class, 'laporan']);
        // CRUD later
    });
});
