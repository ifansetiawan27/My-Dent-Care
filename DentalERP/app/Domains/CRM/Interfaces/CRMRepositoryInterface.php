<?php

declare(strict_types=1);

namespace App\Domains\CRM\Interfaces;

use App\Domains\CRM\Models\CRM;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CRMRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?CRM;
    public function create(array $data): CRM;
    public function update(CRM $crm, array $data): CRM;
    public function delete(CRM $crm): bool;
}