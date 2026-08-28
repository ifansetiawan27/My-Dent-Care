<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\WhatsApp\Controllers\WhatsAppController;

Route::prefix('v1/whatsapp')->middleware('auth:sanctum')->group(function () {
    Route::get('/status', [WhatsAppController::class, 'status']);

    // Session management and message sending are admin-only operations.
    Route::middleware('role:super_admin|admin')->group(function () {
        Route::post('/qr', [WhatsAppController::class, 'generateQR']);
        Route::post('/logout', [WhatsAppController::class, 'disconnect']);
        Route::post('/test-send', [WhatsAppController::class, 'testSend']);
        Route::post('/test-reminder', [WhatsAppController::class, 'testReminder']);
    });
});
