<?php

declare(strict_types=1);

namespace App\Domains\Integration\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Integration\Models\IntegrationConfig */
final class IntegrationConfigResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'integration_type' => $this->integration_type,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'endpoint_url' => $this->endpoint_url,
            'config' => $this->config,
            'last_sync_at' => $this->last_sync_at?->toISOString(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
