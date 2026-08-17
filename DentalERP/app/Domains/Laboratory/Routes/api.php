<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Laboratory\Controllers\LaboratoryController;

Route::prefix('api/v1/lab-orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [LaboratoryController::class, 'index']);
    Route::get('/{id}', [LaboratoryController::class, 'show']);
    Route::post('/', [LaboratoryController::class, 'store']);
    Route::put('/{id}', [LaboratoryController::class, 'update']);
    Route::delete('/{id}', [LaboratoryController::class, 'destroy']);
});