<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Interfaces;

use App\Domains\Reporting\Models\Reporting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportingRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Reporting;
    public function create(array $data): Reporting;
    public function update(Reporting $reporting, array $data): Reporting;
    public function delete(Reporting $reporting): bool;
}