<?php
declare(strict_types=1);
use App\Domains\Subscription\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/subscription')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/', [SubscriptionController::class, 'show']);
    Route::post('cancel', [SubscriptionController::class, 'cancel']);
    Route::get('plans', [SubscriptionController::class, 'plans']);
});