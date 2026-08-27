<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTO;

readonly class UpdateChartOfAccountDTO
{
    public function __construct(
        public ?string $accountCode = null,
        public ?string $accountName = null,
        public ?string $accountType = null,
        public ?string $accountCategory = null,
        public ?string $parentId = null,
        public ?bool $isActive = null,
        public ?string $description = null,
    ) {}
}
