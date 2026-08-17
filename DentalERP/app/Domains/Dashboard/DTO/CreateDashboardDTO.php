<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\DTO;

final readonly class CreateDashboardDTO
{
    public function __construct(
        public string $name,
        public string $organizationId,
        public ?string $userId = null,
        public ?array $config = null,
        public ?array $widgets = null,
        public ?bool $isDefault = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name'            => $this->name,
            'organization_id' => $this->organizationId,
            'user_id'         => $this->userId,
            'config'          => $this->config,
            'widgets'         => $this->widgets,
            'is_default'      => $this->isDefault ?? false,
        ];
    }
}