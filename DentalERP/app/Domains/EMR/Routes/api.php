<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route; use App\Domains\EMR\Controllers\EMRController;
Route::prefix('v1/emrs')->middleware('auth:sanctum')->group(function() {
    Route::get('/',[EMRController::class,'index']);
    Route::get('/{id}',[EMRController::class,'show']);
    Route::post('/',[EMRController::class,'store']);
    Route::put('/{id}',[EMRController::class,'update']);
    Route::delete('/{id}',[EMRController::class,'destroy']);
    Route::patch('/{id}/toggle-active',[EMRController::class,'toggleActive']);
});