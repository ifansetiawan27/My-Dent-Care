<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Integration\Controllers\IntegrationConfigController;
use App\Domains\Integration\Controllers\IntegrationLogController;
use App\Domains\Integration\Controllers\IntegrationMappingController;

Route::prefix('v1/integration/configs')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [IntegrationConfigController::class, 'index']);
    Route::get('/{id}', [IntegrationConfigController::class, 'show']);
    Route::post('/', [IntegrationConfigController::class, 'store']);
    Route::put('/{id}', [IntegrationConfigController::class, 'update']);
    Route::delete('/{id}', [IntegrationConfigController::class, 'destroy']);
    Route::get('/test/{id}', [IntegrationConfigController::class, 'testConnection']);
});

Route::prefix('v1/integration/logs')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [IntegrationLogController::class, 'index']);
    Route::get('/{id}', [IntegrationLogController::class, 'show']);
});

Route::prefix('v1/integration/mappings')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [IntegrationMappingController::class, 'index']);
    Route::get('/{id}', [IntegrationMappingController::class, 'show']);
    Route::post('/', [IntegrationMappingController::class, 'store']);
    Route::put('/{id}', [IntegrationMappingController::class, 'update']);
    Route::delete('/{id}', [IntegrationMappingController::class, 'destroy']);
    Route::get('/find/{type}/{code}', [IntegrationMappingController::class, 'findByExternal']);
});
