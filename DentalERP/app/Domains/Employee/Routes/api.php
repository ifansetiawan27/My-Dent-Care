<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route; use App\Domains\Employee\Controllers\EmployeeController;
Route::prefix('api/v1/employees')->middleware('auth:sanctum')->group(function() {
    Route::get('/',[EmployeeController::class,'index']);
    Route::get('/{id}',[EmployeeController::class,'show']);
    Route::post('/',[EmployeeController::class,'store']);
    Route::put('/{id}',[EmployeeController::class,'update']);
    Route::delete('/{id}',[EmployeeController::class,'destroy']);
    Route::patch('/{id}/toggle-active',[EmployeeController::class,'toggleActive']);
});
