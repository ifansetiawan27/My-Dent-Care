<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\DTO;

final readonly class UpdateDashboardDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $userId = null,
        public ?array $config = null,
        public ?array $widgets = null,
        public ?bool $isDefault = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name'       => $this->name,
            'user_id'    => $this->userId,
            'config'     => $this->config,
            'widgets'    => $this->widgets,
            'is_default' => $this->isDefault,
        ], fn ($v) => $v !== null);
    }
}