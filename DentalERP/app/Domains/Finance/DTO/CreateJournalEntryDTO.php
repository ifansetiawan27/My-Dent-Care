<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTO;

readonly class CreateJournalEntryDTO
{
    public function __construct(
        public string $organizationId,
        public string $entryDate,
        public string $periodDate,
        public array $lines, // [['account_id', 'entry_type', 'amount', 'description'], ...]
        public ?string $referenceType = null,
        public ?string $referenceId = null,
        public ?string $description = null,
    ) {}
}
