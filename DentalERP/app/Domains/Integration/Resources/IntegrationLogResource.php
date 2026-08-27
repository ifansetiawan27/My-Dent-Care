<?php

declare(strict_types=1);

namespace App\Domains\Integration\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Integration\Models\IntegrationLog */
final class IntegrationLogResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id' => $this->id,
            'integration_config_id' => $this->integration_config_id,
            'direction' => $this->direction,
            'endpoint' => $this->endpoint,
            'request_payload' => $this->request_payload,
            'response_payload' => $this->response_payload,
            'status' => $this->status,
            'response_code' => $this->response_code,
            'duration_ms' => $this->duration_ms,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
