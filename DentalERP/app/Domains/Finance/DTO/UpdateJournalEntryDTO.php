<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTO;

readonly class UpdateJournalEntryDTO
{
    public function __construct(
        public ?string $entryDate = null,
        public ?string $periodDate = null,
        public ?array $lines = null,
        public ?string $description = null,
        public ?string $status = null,
    ) {}
}
