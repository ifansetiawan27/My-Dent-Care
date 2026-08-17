<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Asset\Controllers\AssetController;

Route::prefix('api/v1/assets')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [AssetController::class, 'index']);
    Route::get('/{id}', [AssetController::class, 'show']);
    Route::post('/', [AssetController::class, 'store']);
    Route::put('/{id}', [AssetController::class, 'update']);
    Route::delete('/{id}', [AssetController::class, 'destroy']);
});