<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\CRM\Controllers\CRMController;

Route::prefix('v1/crm-contacts')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CRMController::class, 'index']);
    Route::get('/{id}', [CRMController::class, 'show']);
    Route::post('/', [CRMController::class, 'store']);
    Route::put('/{id}', [CRMController::class, 'update']);
    Route::delete('/{id}', [CRMController::class, 'destroy']);
});