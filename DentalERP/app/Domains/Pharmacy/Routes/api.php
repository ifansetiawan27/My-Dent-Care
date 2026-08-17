<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Pharmacy\Controllers\PharmacyController;

Route::prefix('api/v1/pharmacy-items')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [PharmacyController::class, 'index']);
    Route::get('/{id}', [PharmacyController::class, 'show']);
    Route::post('/', [PharmacyController::class, 'store']);
    Route::put('/{id}', [PharmacyController::class, 'update']);
    Route::delete('/{id}', [PharmacyController::class, 'destroy']);
    Route::patch('/{id}/toggle-active', [PharmacyController::class, 'toggleActive']);
});