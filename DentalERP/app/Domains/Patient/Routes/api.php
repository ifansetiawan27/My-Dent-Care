<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route; use App\Domains\Patient\Controllers\PatientController;
Route::prefix('api/v1/patients')->middleware('auth:sanctum')->group(function() {
    Route::get('/',[PatientController::class,'index']);
    Route::get('/{id}',[PatientController::class,'show']);
    Route::post('/',[PatientController::class,'store']);
    Route::put('/{id}',[PatientController::class,'update']);
    Route::delete('/{id}',[PatientController::class,'destroy']);
    Route::patch('/{id}/toggle-active',[PatientController::class,'toggleActive']);
});