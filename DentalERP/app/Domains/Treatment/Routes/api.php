<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Treatment\Controllers\TreatmentController;

Route::prefix('v1/treatments')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [TreatmentController::class, 'index']);
    Route::get('/{id}', [TreatmentController::class, 'show']);
    Route::post('/', [TreatmentController::class, 'store']);
    Route::put('/{id}', [TreatmentController::class, 'update']);
    Route::delete('/{id}', [TreatmentController::class, 'destroy']);
});