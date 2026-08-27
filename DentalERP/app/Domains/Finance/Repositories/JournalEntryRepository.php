<?php

declare(strict_types=1);

namespace App\Domains\Finance\Repositories;

use App\Core\Base\BaseRepository;
use App\Domains\Finance\Interfaces\JournalEntryRepositoryInterface;
use App\Domains\Finance\Models\JournalEntry;

class JournalEntryRepository extends BaseRepository implements JournalEntryRepositoryInterface
{
    public function __construct(JournalEntry $model)
    {
        parent::__construct($model);
    }

    public function findByNumber(string $entryNumber, string $organizationId): ?JournalEntry
    {
        return $this->model->where('entry_number', $entryNumber)
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function findByStatus(string $status, string $organizationId): array
    {
        return $this->model->where('status', $status)
            ->where('organization_id', $organizationId)
            ->orderByDesc('entry_date')
            ->get()->all();
    }

    public function findByPeriod(string $organizationId, string $startDate, string $endDate): array
    {
        return $this->model->where('organization_id', $organizationId)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderByDesc('entry_date')
            ->get()->all();
    }

    public function findUnbalanced(string $organizationId): array
    {
        return $this->model->where('organization_id', $organizationId)
            ->where('is_balanced', false)
            ->where('status', 'draft')
            ->get()->all();
    }
}
