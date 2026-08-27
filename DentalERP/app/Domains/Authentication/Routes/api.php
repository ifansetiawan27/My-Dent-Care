<?php

declare(strict_types=1);

use App\Domains\Authentication\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->name('auth.')->group(function (): void {
    // Rate-limited public auth endpoints (10 requests/minute for login, 5/hour for password reset)
    Route::middleware('throttle:10,1')->group(function (): void {
        Route::post('/login',            [AuthController::class, 'login'])->name('login');
        Route::post('/lookup',           [AuthController::class, 'lookup'])->name('lookup');
    });

    Route::middleware('throttle:5,60')->group(function (): void {
        Route::post('/forgot-password',  [AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/reset-password',   [AuthController::class, 'resetPassword'])->name('reset-password');
    });

    Route::post('/refresh',          [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/logout',           [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
    Route::post('/logout-all',       [AuthController::class, 'logoutAll'])->name('logout-all')->middleware('auth:sanctum');
    Route::post('/change-password',  [AuthController::class, 'changePassword'])->name('change-password')->middleware('auth:sanctum');
    Route::get('/profile',           [AuthController::class, 'profile'])->name('profile')->middleware('auth:sanctum');
    Route::put('/profile',           [AuthController::class, 'updateProfile'])->name('update-profile')->middleware('auth:sanctum');
    Route::get('/login-history',     [AuthController::class, 'loginHistory'])->name('login-history')->middleware('auth:sanctum');
    Route::get('/devices',           [AuthController::class, 'devices'])->name('devices')->middleware('auth:sanctum');
    Route::delete('/devices/{deviceId}', [AuthController::class, 'revokeDevice'])->name('devices.destroy')->middleware('auth:sanctum');
});
