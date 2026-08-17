<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\DTO;

final readonly class UpdateIntegrationDTO
{
    public function __construct(
        public ?string $provider    = null,
        public ?string $name        = null,
        public ?array  $config      = null,
        public ?array  $credentials = null,
        public ?bool   $isActive    = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'provider'    => $this->provider,
            'name'        => $this->name,
            'config'      => $this->config,
            'credentials' => $this->credentials,
            'is_active'   => $this->isActive,
        ], fn ($v): bool => $v !== null);
    }
}