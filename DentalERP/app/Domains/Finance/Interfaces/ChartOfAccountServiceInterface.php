<?php

declare(strict_types=1);

namespace App\Domains\Finance\Interfaces;

use App\Core\Contracts\ServiceInterface;
use App\Domains\Finance\Models\ChartOfAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChartOfAccountServiceInterface extends ServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ChartOfAccount;
    public function create(array $data): ChartOfAccount;
    public function update(string $id, array $data, string $organizationId): ChartOfAccount;
    public function delete(string $id, string $organizationId): bool;
}
