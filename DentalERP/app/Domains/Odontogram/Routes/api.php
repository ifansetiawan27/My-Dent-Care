<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route; use App\Domains\Odontogram\Controllers\OdontogramController;
Route::prefix('api/v1/odontograms')->middleware('auth:sanctum')->group(function() {
    Route::get('/',[OdontogramController::class,'index']);
    Route::get('/{id}',[OdontogramController::class,'show']);
    Route::post('/',[OdontogramController::class,'store']);
    Route::put('/{id}',[OdontogramController::class,'update']);
    Route::delete('/{id}',[OdontogramController::class,'destroy']);
    Route::patch('/{id}/toggle-active',[OdontogramController::class,'toggleActive']);
});