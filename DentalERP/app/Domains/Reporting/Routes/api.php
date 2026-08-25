<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Reporting\Controllers\ReportingController;

Route::prefix('v1/reports')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ReportingController::class, 'index']);
    Route::get('/{id}', [ReportingController::class, 'show']);
    Route::post('/', [ReportingController::class, 'store']);
    Route::put('/{id}', [ReportingController::class, 'update']);
    Route::delete('/{id}', [ReportingController::class, 'destroy']);
});