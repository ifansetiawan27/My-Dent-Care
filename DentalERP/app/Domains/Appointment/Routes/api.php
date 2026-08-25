<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route; use App\Domains\Appointment\Controllers\AppointmentController;
Route::prefix('v1/appointments')->middleware('auth:sanctum')->group(function() {
    Route::get('/',[AppointmentController::class,'index']);
    Route::get('/{id}',[AppointmentController::class,'show']);
    Route::post('/',[AppointmentController::class,'store']);
    Route::put('/{id}',[AppointmentController::class,'update']);
    Route::delete('/{id}',[AppointmentController::class,'destroy']);
    Route::patch('/{id}/toggle-active',[AppointmentController::class,'toggleActive']);
});