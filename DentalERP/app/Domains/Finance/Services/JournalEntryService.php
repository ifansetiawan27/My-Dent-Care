<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Finance\Enums\JournalEntryStatus;
use App\Domains\Finance\Interfaces\JournalEntryRepositoryInterface;
use App\Domains\Finance\Interfaces\JournalEntryServiceInterface;
use App\Domains\Finance\Models\JournalEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class JournalEntryService implements JournalEntryServiceInterface
{
    public function __construct(
        private readonly JournalEntryRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): JournalEntry
    {
        $entry = $this->repository->findById($id, $organizationId);
        if (! $entry) {
            throw new NotFoundException('Journal Entry not found.');
        }
        return $entry;
    }

    public function create(array $data): JournalEntry
    {
        $data['entry_number'] = $this->generateEntryNumber();

        return DB::transaction(fn (): JournalEntry => $this->repository->create($data));
    }

    public function update(string $id, array $data, string $organizationId): JournalEntry
    {
        $entry = $this->findById($id, $organizationId);

        if (JournalEntryStatus::from($entry->status) !== JournalEntryStatus::DRAFT) {
            throw new BusinessException('Can only update journal entries in draft status.');
        }

        return DB::transaction(fn (): JournalEntry => $this->repository->update($entry, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $entry = $this->findById($id, $organizationId);

        if (JournalEntryStatus::from($entry->status) !== JournalEntryStatus::DRAFT) {
            throw new BusinessException('Can only delete journal entries in draft status.');
        }

        return $this->repository->delete($entry);
    }

    public function postJournal(string $id, string $organizationId): JournalEntry
    {
        $entry = $this->findById($id, $organizationId);

        if (JournalEntryStatus::from($entry->status) !== JournalEntryStatus::DRAFT) {
            throw new BusinessException('Can only post journal entries in draft status.');
        }

        if (! $entry->is_balanced) {
            throw new BusinessException('Cannot post an unbalanced journal entry. Total debit must equal total credit.');
        }

        if (floatval((string) $entry->total_debit) !== floatval((string) $entry->total_credit)) {
            throw new BusinessException('Cannot post journal entry. Total debit must equal total credit.');
        }

        $data = [
            'status' => JournalEntryStatus::POSTED->value,
            'posted_by' => auth()->user()->id,
            'posted_at' => now(),
        ];

        return DB::transaction(fn (): JournalEntry => $this->repository->update($entry, $data));
    }

    public function cancelJournal(string $id, string $organizationId): JournalEntry
    {
        $entry = $this->findById($id, $organizationId);

        $currentStatus = JournalEntryStatus::from($entry->status);

        if (! $currentStatus->canBeCancelled()) {
            throw new BusinessException('Can only cancel journal entries in draft or posted status.');
        }

        $data = [
            'status' => JournalEntryStatus::CANCELLED->value,
        ];

        return DB::transaction(fn (): JournalEntry => $this->repository->update($entry, $data));
    }

    private function generateEntryNumber(): string
    {
        $prefix = 'JE-' . now()->format('Y') . '-';
        $last = JournalEntry::where('entry_number', 'LIKE', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->first();

        $seq = $last ? (int) substr($last->entry_number, -6) + 1 : 1;

        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
