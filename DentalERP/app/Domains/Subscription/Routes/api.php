<?php
declare(strict_types=1);
use App\Domains\Subscription\Controllers\PaymentWebhookController;
use App\Domains\Subscription\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/subscription')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/', [SubscriptionController::class, 'show']);
    Route::post('cancel', [SubscriptionController::class, 'cancel']);
    Route::get('plans', [SubscriptionController::class, 'plans']);
});

// Payment gateway webhooks are called by the provider (not a logged-in user),
// so they live outside the auth:sanctum group and are protected by a signature
// check in the controller plus rate limiting.
Route::prefix('v1/webhooks')->group(function (): void {
    Route::post('/midtrans', [PaymentWebhookController::class, 'midtrans'])
        ->middleware('throttle:60,1')
        ->name('webhooks.midtrans');
});