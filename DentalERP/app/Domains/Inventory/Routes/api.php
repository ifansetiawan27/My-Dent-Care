<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Inventory\Controllers\InventoryController;

Route::prefix('api/v1/inventory-items')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [InventoryController::class, 'index']);
    Route::get('/{id}', [InventoryController::class, 'show']);
    Route::post('/', [InventoryController::class, 'store']);
    Route::put('/{id}', [InventoryController::class, 'update']);
    Route::delete('/{id}', [InventoryController::class, 'destroy']);
    Route::patch('/{id}/toggle-active', [InventoryController::class, 'toggleActive']);
});