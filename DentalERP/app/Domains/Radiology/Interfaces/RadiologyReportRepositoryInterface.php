<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Interfaces;

use App\Domains\Radiology\Models\RadiologyReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RadiologyReportRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?RadiologyReport;
    public function create(array $data): RadiologyReport;
    public function update(RadiologyReport $report, array $data): RadiologyReport;
    public function delete(RadiologyReport $report): bool;
}
