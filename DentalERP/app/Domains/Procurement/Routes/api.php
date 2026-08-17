<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Procurement\Controllers\ProcurementController;

Route::prefix('api/v1/procurement-orders')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProcurementController::class, 'index']);
    Route::get('/{id}', [ProcurementController::class, 'show']);
    Route::post('/', [ProcurementController::class, 'store']);
    Route::put('/{id}', [ProcurementController::class, 'update']);
    Route::delete('/{id}', [ProcurementController::class, 'destroy']);
});