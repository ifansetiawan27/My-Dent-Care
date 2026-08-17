<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Dashboard\Controllers\DashboardController;

Route::prefix('api/v1/dashboards')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/{id}', [DashboardController::class, 'show']);
    Route::post('/', [DashboardController::class, 'store']);
    Route::put('/{id}', [DashboardController::class, 'update']);
    Route::delete('/{id}', [DashboardController::class, 'destroy']);
});