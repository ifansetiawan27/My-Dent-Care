<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route;
use App\Domains\AI\Controllers\AIController;
Route::prefix('v1/ai-queries')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [AIController::class, 'index'])->name('ai-queries.index');
    Route::get('/{id}', [AIController::class, 'show'])->name('ai-queries.show');
    Route::post('/', [AIController::class, 'store'])->name('ai-queries.store');
    Route::post('/{id}/execute', [AIController::class, 'execute'])->name('ai-queries.execute');
    Route::post('/{id}/retry', [AIController::class, 'retry'])->name('ai-queries.retry');
    Route::post('/{id}/cancel', [AIController::class, 'cancel'])->name('ai-queries.cancel');
});