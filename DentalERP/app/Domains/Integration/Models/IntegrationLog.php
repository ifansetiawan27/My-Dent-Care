<?php

declare(strict_types=1);

namespace App\Domains\Integration\Models;

use App\Core\Base\BaseModel;
use App\Domains\Integration\Enums\IntegrationDirection;
use App\Domains\Integration\Enums\IntegrationStatus;

class IntegrationLog extends BaseModel
{
    protected $table = 'integration_logs';

    protected $casts = [
        'direction' => IntegrationDirection::class,
        'status' => IntegrationStatus::class,
        'request_payload' => 'array',
        'response_payload' => 'array',
        'duration_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function config(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(IntegrationConfig::class, 'integration_config_id');
    }
}
