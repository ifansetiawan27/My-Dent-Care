<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Interfaces;

use App\Domains\Dashboard\Models\Dashboard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DashboardRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Dashboard;
    public function create(array $data): Dashboard;
    public function update(Dashboard $dashboard, array $data): Dashboard;
    public function delete(Dashboard $dashboard): bool;
}