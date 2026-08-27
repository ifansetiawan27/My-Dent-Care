<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTO;

readonly class CreateChartOfAccountDTO
{
    public function __construct(
        public string $organizationId,
        public string $accountCode,
        public string $accountName,
        public string $accountType,
        public ?string $accountCategory = null,
        public ?string $parentId = null,
        public bool $isActive = true,
        public bool $isSystem = false,
        public ?string $description = null,
    ) {}
}
