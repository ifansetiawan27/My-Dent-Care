<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Interfaces;

use App\Domains\Reporting\DTO\CreateReportingDTO;
use App\Domains\Reporting\DTO\UpdateReportingDTO;
use App\Domains\Reporting\Models\Reporting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportingServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Reporting;
    public function create(CreateReportingDTO $dto): Reporting;
    public function update(string $id, UpdateReportingDTO $dto, string $organizationId): Reporting;
    public function delete(string $id, string $organizationId): bool;
}