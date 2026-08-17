<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Dashboard\DTO\CreateDashboardDTO;
use App\Domains\Dashboard\DTO\UpdateDashboardDTO;
use App\Domains\Dashboard\Interfaces\DashboardRepositoryInterface;
use App\Domains\Dashboard\Interfaces\DashboardServiceInterface;
use App\Domains\Dashboard\Models\Dashboard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Dashboard
    {
        $dashboard = $this->repository->findById($id, $organizationId);
        if (! $dashboard) {
            throw new NotFoundException('Dashboard not found.');
        }
        return $dashboard;
    }

    public function create(CreateDashboardDTO $dto): Dashboard
    {
        return DB::transaction(fn (): Dashboard => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateDashboardDTO $dto, string $organizationId): Dashboard
    {
        $dashboard = $this->findById($id, $organizationId);

        return DB::transaction(fn (): Dashboard => $this->repository->update($dashboard, $dto->toArray()));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }
}