<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Interfaces;

use App\Domains\Radiology\DTO\CreateRadiologyReportDTO;
use App\Domains\Radiology\DTO\UpdateRadiologyReportDTO;
use App\Domains\Radiology\Models\RadiologyReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RadiologyReportServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): RadiologyReport;
    public function create(CreateRadiologyReportDTO $dto): RadiologyReport;
    public function update(string $id, UpdateRadiologyReportDTO $dto, string $organizationId): RadiologyReport;
    public function delete(string $id, string $organizationId): bool;
    public function finalizeReport(string $id, string $organizationId): RadiologyReport;
}
