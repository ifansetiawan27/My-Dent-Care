<?php

declare(strict_types=1);

use App\Domains\Branch\Controllers\BranchController;
use Illuminate\Support\Facades\Route;

/**
 * Branch API Routes
 *
 * Prefix  : /api/v1/branches
 * Auth    : auth:sanctum
 * Permission: Spatie Laravel Permission
 *
 * How to register in bootstrap/app.php:
 *   ->withRouting(
 *       api: [
 *           base_path('app/Domains/Branch/Routes/api.php'),
 *       ],
 *   )
 *
 * Or in a RouteServiceProvider:
 *   Route::middleware('api')
 *       ->prefix('api/v1')
 *       ->group(base_path('app/Domains/Branch/Routes/api.php'));
 */

Route::prefix('api/v1/branches')
    ->name('branches.')
    ->middleware(['auth:sanctum'])
    ->group(function (): void {

        // ---------------------------------------------------------------
        // Read — GET /api/v1/branches
        // Permission: branch.view
        // Query: ?organization_id=&search=&per_page=&sort_by=&sort_dir=
        // ---------------------------------------------------------------
        Route::get('/', [BranchController::class, 'index'])
            ->name('index')
            ->middleware('permission:branch.view');

        // ---------------------------------------------------------------
        // Read — GET /api/v1/branches/{id}
        // Permission: branch.view
        // ---------------------------------------------------------------
        Route::get('/{id}', [BranchController::class, 'show'])
            ->name('show')
            ->middleware('permission:branch.view');

        // ---------------------------------------------------------------
        // Write — POST /api/v1/branches
        // Permission: branch.create
        // ---------------------------------------------------------------
        Route::post('/', [BranchController::class, 'store'])
            ->name('store')
            ->middleware('permission:branch.create');

        // ---------------------------------------------------------------
        // Write — PUT /api/v1/branches/{id}
        // Permission: branch.update
        // ---------------------------------------------------------------
        Route::put('/{id}', [BranchController::class, 'update'])
            ->name('update')
            ->middleware('permission:branch.update');

        // ---------------------------------------------------------------
        // Write — DELETE /api/v1/branches/{id}
        // Permission: branch.delete
        // Enforces delete guards via Service (has Users, Patients, etc.)
        // ---------------------------------------------------------------
        Route::delete('/{id}', [BranchController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:branch.delete');

        // ---------------------------------------------------------------
        // Write — POST /api/v1/branches/{id}/restore
        // Permission: branch.restore
        // Restores a soft-deleted branch.
        // ---------------------------------------------------------------
        Route::post('/{id}/restore', [BranchController::class, 'restore'])
            ->name('restore')
            ->middleware('permission:branch.restore');
    });
