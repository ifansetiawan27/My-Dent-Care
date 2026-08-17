<?php

declare(strict_types=1);

namespace App\Domains\Billing\Interfaces;

use App\Domains\Billing\Models\Billing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BillingRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Billing;
    public function create(array $data): Billing;
    public function update(Billing $billing, array $data): Billing;
    public function delete(Billing $billing): bool;
}