<?php

declare(strict_types=1);

use App\Domains\MasterData\Controllers\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/master-data')->middleware(['auth:sanctum'])->group(function (): void {
    Route::get('{resource}', [MasterDataController::class, 'index']);
    Route::get('{resource}/{id}', [MasterDataController::class, 'show']);
    Route::post('{resource}', [MasterDataController::class, 'store']);
    Route::put('{resource}/{id}', [MasterDataController::class, 'update']);
    Route::delete('{resource}/{id}', [MasterDataController::class, 'destroy']);
    Route::patch('{resource}/{id}/toggle-active', [MasterDataController::class, 'toggleActive']);
});
