<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route;
use App\Domains\Organization\Http\Controllers\ClinicSettingsController;

Route::prefix('v1/settings')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/', [ClinicSettingsController::class, 'show']);
    Route::put('/', [ClinicSettingsController::class, 'update']);
});