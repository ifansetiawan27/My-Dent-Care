<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use App\Core\Base\BaseModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * BaseMasterDataModel
 *
 * Abstract base for all Master Data reference models.
 * All 18 reference tables share this common structure:
 *   id, code, name, is_active, created_by, updated_by, deleted_by, timestamps, soft_delete
 *
 * Usage — extend for each reference table:
 *
 *   class Country extends BaseMasterDataModel
 *   {
 *       protected $table = 'countries';
 *   }
 *
 * Master Data is:
 *  - Global (not scoped to organization or branch)
 *  - Read-heavy (candidates for Redis caching)
 *  - Soft-delete only (never hard-deleted)
 *  - Managed by Super Admin only
 *
 * @property string              $id
 * @property string              $code
 * @property string              $name
 * @property bool                $is_active
 * @property string|null         $created_by
 * @property string|null         $updated_by
 * @property string|null         $deleted_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
abstract class BaseMasterDataModel extends BaseModel
{
    // -------------------------------------------------------------------------
    // Mass Assignment
    // -------------------------------------------------------------------------

    /**
     * The attributes that are mass assignable.
     * Child models may merge additional columns.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // -------------------------------------------------------------------------
    // Hidden
    // -------------------------------------------------------------------------

    /**
     * Override BaseModel hidden — Master Data has no sensitive fields.
     *
     * @var array<string>
     */
    protected $hidden = [
        'deleted_at',
        'deleted_by',
    ];

    // -------------------------------------------------------------------------
    // Casts
    // -------------------------------------------------------------------------

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope: only active records.
     * Use for dropdowns, select lists, and API responses.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: only inactive records.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: search by code or name using ILIKE (PostgreSQL).
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $q) use ($keyword): void {
            $q->where('code', 'ILIKE', "%{$keyword}%")
              ->orWhere('name', 'ILIKE', "%{$keyword}%");
        });
    }

    /**
     * Scope: order by name ascending (natural order for reference lists).
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}
