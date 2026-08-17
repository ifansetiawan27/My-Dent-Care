<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Models;

use App\Core\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string              $id
 * @property string              $organization_id
 * @property string              $provider
 * @property string              $name
 * @property array|null          $config
 * @property array|null          $credentials
 * @property bool                $is_active
 * @property \Carbon\Carbon|null $last_sync_at
 * @property string|null         $created_by
 * @property string|null         $updated_by
 * @property string|null         $deleted_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class IntegrationHub extends BaseModel
{
    protected $table = 'integration_configs';

    protected $fillable = [
        'organization_id',
        'provider',
        'name',
        'config',
        'credentials',
        'is_active',
        'last_sync_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'credentials',
        'deleted_at',
        'deleted_by',
    ];

    protected $casts = [
        'config'        => 'array',
        'credentials'   => 'encrypted:array',
        'is_active'     => 'boolean',
        'last_sync_at'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        /** @phpstan-ignore-next-line */
        return $this->belongsTo(
            \App\Domains\Organization\Models\Organization::class,
            'organization_id',
            'id',
        );
    }
}