<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Billing\Controllers\BillingController;

Route::prefix('v1/invoices')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [BillingController::class, 'index']);
    Route::get('/{id}', [BillingController::class, 'show']);
    Route::post('/', [BillingController::class, 'store']);
    Route::put('/{id}', [BillingController::class, 'update']);
    Route::delete('/{id}', [BillingController::class, 'destroy']);
});