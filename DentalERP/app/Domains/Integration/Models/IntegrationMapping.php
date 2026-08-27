<?php

declare(strict_types=1);

namespace App\Domains\Integration\Models;

use App\Core\Base\BaseModel;

class IntegrationMapping extends BaseModel
{
    protected $table = 'integration_mappings';

    protected $casts = [
        'external_data' => 'array',
        'is_synced' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function config(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(IntegrationConfig::class, 'integration_config_id');
    }
}
