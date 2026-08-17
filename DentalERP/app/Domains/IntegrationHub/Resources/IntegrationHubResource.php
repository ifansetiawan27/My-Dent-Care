<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\IntegrationHub\Models\IntegrationHub */
final class IntegrationHubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'provider'     => $this->provider,
            'name'         => $this->name,
            'config'       => $this->config,
            'is_active'    => $this->is_active,
            'last_sync_at' => $this->last_sync_at?->toISOString(),
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}