<?php

declare(strict_types=1);

namespace App\Domains\Finance\Interfaces;

use App\Core\Contracts\ServiceInterface;
use App\Domains\Finance\Models\JournalEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface JournalEntryServiceInterface extends ServiceInterface
{
    public function paginate(array $params = []): LengthAwarePaginator;
    public function findByIdWithOrganization(string $id, string $organizationId): JournalEntry;
    public function createForOrganization(array $data, string $organizationId): JournalEntry;
    public function updateForOrganization(string $id, array $data, string $organizationId): JournalEntry;
    public function deleteForOrganization(string $id, string $organizationId): bool;
    public function postJournal(string $id, string $organizationId): JournalEntry;
    public function cancelJournal(string $id, string $organizationId): JournalEntry;
}
