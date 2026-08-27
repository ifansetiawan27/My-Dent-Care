<?php

declare(strict_types=1);

use App\Domains\Finance\Controllers\ChartOfAccountController;
use App\Domains\Finance\Controllers\FinancialReportController;
use App\Domains\Finance\Controllers\JournalEntryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/finance/chart-of-accounts')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ChartOfAccountController::class, 'index']);
    Route::get('/{id}', [ChartOfAccountController::class, 'show']);
    Route::post('/', [ChartOfAccountController::class, 'store']);
    Route::put('/{id}', [ChartOfAccountController::class, 'update']);
    Route::delete('/{id}', [ChartOfAccountController::class, 'destroy']);
});

Route::prefix('v1/finance/journal-entries')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [JournalEntryController::class, 'index']);
    Route::get('/{id}', [JournalEntryController::class, 'show']);
    Route::post('/', [JournalEntryController::class, 'store']);
    Route::put('/{id}', [JournalEntryController::class, 'update']);
    Route::delete('/{id}', [JournalEntryController::class, 'destroy']);
    Route::post('/{id}/post', [JournalEntryController::class, 'post']);
    Route::post('/{id}/cancel', [JournalEntryController::class, 'cancel']);
});

Route::prefix('v1/finance/reports')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [FinancialReportController::class, 'index']);
    Route::get('/{id}', [FinancialReportController::class, 'show']);
    Route::post('/', [FinancialReportController::class, 'store']);
    Route::put('/{id}', [FinancialReportController::class, 'update']);
    Route::delete('/{id}', [FinancialReportController::class, 'destroy']);
    Route::post('/{id}/generate', [FinancialReportController::class, 'generate']);
});
