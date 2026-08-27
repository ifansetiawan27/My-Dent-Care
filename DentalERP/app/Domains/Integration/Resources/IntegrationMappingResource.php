<?php

declare(strict_types=1);

namespace App\Domains\Integration\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Integration\Models\IntegrationMapping */
final class IntegrationMappingResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id' => $this->id,
            'integration_config_id' => $this->integration_config_id,
            'local_type' => $this->local_type,
            'local_id' => $this->local_id,
            'external_code' => $this->external_code,
            'external_data' => $this->external_data,
            'is_synced' => $this->is_synced,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
