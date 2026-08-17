<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\DTO;

final readonly class CreateIntegrationDTO
{
    public function __construct(
        public string  $provider,
        public string  $name,
        public string  $organizationId,
        public ?array  $config      = null,
        public ?array  $credentials = null,
    ) {}

    public function toArray(): array
    {
        return [
            'provider'        => $this->provider,
            'name'            => $this->name,
            'organization_id' => $this->organizationId,
            'config'          => $this->config,
            'credentials'     => $this->credentials,
            'is_active'       => false,
        ];
    }
}