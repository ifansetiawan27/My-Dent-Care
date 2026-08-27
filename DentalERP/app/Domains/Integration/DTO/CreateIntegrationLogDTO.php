<?php

declare(strict_types=1);

namespace App\Domains\Integration\DTO;

final readonly class CreateIntegrationLogDTO
{
    public function __construct(
        public string $integrationConfigId,
        public string $direction,
        public string $status = 'pending',
        public ?string $endpoint = null,
        public ?array $requestPayload = null,
        public ?array $responsePayload = null,
        public ?string $responseCode = null,
        public ?int $durationMs = null,
        public ?string $errorMessage = null,
    ) {}

    public function toArray(): array
    {
        return [
            'integration_config_id' => $this->integrationConfigId,
            'direction' => $this->direction,
            'endpoint' => $this->endpoint,
            'request_payload' => $this->requestPayload,
            'response_payload' => $this->responsePayload,
            'status' => $this->status,
            'response_code' => $this->responseCode,
            'duration_ms' => $this->durationMs,
            'error_message' => $this->errorMessage,
        ];
    }
}
