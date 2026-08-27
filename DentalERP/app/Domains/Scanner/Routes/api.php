<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route;
use App\Domains\Scanner\Controllers\ScannerDeviceController;
use App\Domains\Scanner\Controllers\ScanSessionController;
use App\Domains\Scanner\Controllers\ScanFileController;
Route::prefix('v1/scanner/devices')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ScannerDeviceController::class, 'index']);
    Route::get('/{id}', [ScannerDeviceController::class, 'show']);
    Route::post('/', [ScannerDeviceController::class, 'store']);
    Route::put('/{id}', [ScannerDeviceController::class, 'update']);
    Route::delete('/{id}', [ScannerDeviceController::class, 'destroy']);
    Route::post('/calibrate/{id}', [ScannerDeviceController::class, 'calibrate']);
});
Route::prefix('v1/scanner/sessions')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ScanSessionController::class, 'index']);
    Route::get('/{id}', [ScanSessionController::class, 'show']);
    Route::post('/', [ScanSessionController::class, 'store']);
    Route::put('/{id}', [ScanSessionController::class, 'update']);
    Route::delete('/{id}', [ScanSessionController::class, 'destroy']);
    Route::post('/complete/{id}', [ScanSessionController::class, 'complete']);
    Route::post('/fail/{id}', [ScanSessionController::class, 'fail']);
});
Route::prefix('v1/scanner/files')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ScanFileController::class, 'index']);
    Route::get('/{id}', [ScanFileController::class, 'show']);
    Route::post('/', [ScanFileController::class, 'store']);
    Route::put('/{id}', [ScanFileController::class, 'update']);
    Route::delete('/{id}', [ScanFileController::class, 'destroy']);
    Route::post('/mark-processed/{id}', [ScanFileController::class, 'markProcessed']);
});
