<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Interfaces;

use App\Domains\Dashboard\DTO\CreateDashboardDTO;
use App\Domains\Dashboard\DTO\UpdateDashboardDTO;
use App\Domains\Dashboard\Models\Dashboard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DashboardServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Dashboard;
    public function create(CreateDashboardDTO $dto): Dashboard;
    public function update(string $id, UpdateDashboardDTO $dto, string $organizationId): Dashboard;
    public function delete(string $id, string $organizationId): bool;
}