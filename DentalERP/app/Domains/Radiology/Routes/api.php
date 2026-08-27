<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Radiology\Controllers\RadiologyOrderController;
use App\Domains\Radiology\Controllers\RadiologyImageController;
use App\Domains\Radiology\Controllers\RadiologyReportController;

Route::prefix('v1/radiology')->middleware('auth:sanctum')->group(function () {
    // Radiology Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [RadiologyOrderController::class, 'index']);
        Route::get('/{id}', [RadiologyOrderController::class, 'show']);
        Route::post('/', [RadiologyOrderController::class, 'store']);
        Route::put('/{id}', [RadiologyOrderController::class, 'update']);
        Route::delete('/{id}', [RadiologyOrderController::class, 'destroy']);
        Route::post('/{id}/complete', [RadiologyOrderController::class, 'complete']);
    });

    // Radiology Images
    Route::prefix('images')->group(function () {
        Route::get('/', [RadiologyImageController::class, 'index']);
        Route::get('/{id}', [RadiologyImageController::class, 'show']);
        Route::post('/', [RadiologyImageController::class, 'store']);
        Route::put('/{id}', [RadiologyImageController::class, 'update']);
        Route::delete('/{id}', [RadiologyImageController::class, 'destroy']);
    });

    // Radiology Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [RadiologyReportController::class, 'index']);
        Route::get('/{id}', [RadiologyReportController::class, 'show']);
        Route::post('/', [RadiologyReportController::class, 'store']);
        Route::put('/{id}', [RadiologyReportController::class, 'update']);
        Route::delete('/{id}', [RadiologyReportController::class, 'destroy']);
        Route::post('/{id}/finalize', [RadiologyReportController::class, 'finalize']);
    });
});
