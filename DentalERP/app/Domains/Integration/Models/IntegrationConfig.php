<?php

declare(strict_types=1);

namespace App\Domains\Integration\Models;

use App\Core\Base\BaseModel;
use App\Domains\Integration\Enums\IntegrationType;

class IntegrationConfig extends BaseModel
{
    protected $table = 'integration_configs';

    protected $casts = [
        'integration_type' => IntegrationType::class,
        'is_active' => 'boolean',
        'config' => 'array',
        'last_sync_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IntegrationLog::class, 'integration_config_id');
    }

    public function mappings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IntegrationMapping::class, 'integration_config_id');
    }
}
