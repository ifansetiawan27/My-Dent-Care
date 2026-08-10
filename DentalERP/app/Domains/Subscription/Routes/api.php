<?php
declare(strict_types=1);
use App\Domains\Subscription\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')->group(function (): void {
    Route::post('midtrans', [PaymentWebhookController::class, 'midtrans']);
});