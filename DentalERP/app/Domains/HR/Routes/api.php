<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\HR\Controllers\HRController;

Route::prefix('api/v1/hr-records')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [HRController::class, 'index']);
    Route::get('/{id}', [HRController::class, 'show']);
    Route::post('/', [HRController::class, 'store']);
    Route::put('/{id}', [HRController::class, 'update']);
    Route::delete('/{id}', [HRController::class, 'destroy']);
});