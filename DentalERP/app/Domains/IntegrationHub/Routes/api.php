<?php

declare(strict_types=1);

use App\Domains\IntegrationHub\Controllers\IntegrationHubController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/integration-configs')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/',                          [IntegrationHubController::class, 'index']);
    Route::get('/{id}',                      [IntegrationHubController::class, 'show']);
    Route::post('/',                         [IntegrationHubController::class, 'store']);
    Route::put('/{id}',                      [IntegrationHubController::class, 'update']);
    Route::delete('/{id}',                   [IntegrationHubController::class, 'destroy']);
    Route::post('/{id}/toggle-active',       [IntegrationHubController::class, 'toggleActive']);
});