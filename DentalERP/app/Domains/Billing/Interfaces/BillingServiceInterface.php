<?php

declare(strict_types=1);

namespace App\Domains\Billing\Interfaces;

use App\Domains\Billing\DTO\CreateBillingDTO;
use App\Domains\Billing\DTO\UpdateBillingDTO;
use App\Domains\Billing\Models\Billing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BillingServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Billing;
    public function create(CreateBillingDTO $dto): Billing;
    public function update(string $id, UpdateBillingDTO $dto, string $organizationId): Billing;
    public function delete(string $id, string $organizationId): bool;
}