<?php

declare(strict_types=1);

namespace App\Domains\Finance\Interfaces;

use App\Core\Contracts\RepositoryInterface;
use App\Domains\Finance\Models\JournalEntry;

interface JournalEntryRepositoryInterface extends RepositoryInterface
{
    public function findByNumber(string $entryNumber, string $organizationId): ?JournalEntry;
    public function findByStatus(string $status, string $organizationId): array;
    public function findByPeriod(string $organizationId, string $startDate, string $endDate): array;
    public function findUnbalanced(string $organizationId): array;
}
