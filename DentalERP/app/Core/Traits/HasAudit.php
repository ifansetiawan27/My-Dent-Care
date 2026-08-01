<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAudit
{
    /**
     * Boot the HasAudit trait.
     */
    public static function bootHasAudit(): void
    {
        static::creating(function ($model): void {
            if (Auth::check()) {
                $userId = Auth::id();

                if ($model->isFillable('created_by') || isset($model->created_by)) {
                    $model->created_by = $userId;
                }

                if ($model->isFillable('updated_by') || isset($model->updated_by)) {
                    $model->updated_by = $userId;
                }
            }
        });

        static::updating(function ($model): void {
            if (Auth::check()) {
                $userId = Auth::id();

                if ($model->isFillable('updated_by') || isset($model->updated_by)) {
                    $model->updated_by = $userId;
                }
            }
        });

        static::deleting(function ($model): void {
            if (Auth::check() && method_exists($model, 'runSoftDelete')) {
                $userId = Auth::id();

                if ($model->isFillable('deleted_by') || isset($model->deleted_by)) {
                    $model->deleted_by = $userId;
                    $model->saveQuietly();
                }
            }
        });
    }
}
