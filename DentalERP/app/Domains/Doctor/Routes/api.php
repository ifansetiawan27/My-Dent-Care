<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route; use App\Domains\Doctor\Controllers\DoctorController;
Route::prefix('v1/doctors')->middleware('auth:sanctum')->group(function() {
    Route::get('/',[DoctorController::class,'index']);
    Route::get('/{id}',[DoctorController::class,'show']);
    Route::post('/',[DoctorController::class,'store']);
    Route::put('/{id}',[DoctorController::class,'update']);
    Route::delete('/{id}',[DoctorController::class,'destroy']);
    Route::patch('/{id}/toggle-active',[DoctorController::class,'toggleActive']);
});
