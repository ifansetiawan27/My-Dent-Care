<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Finance\Interfaces\ChartOfAccountRepositoryInterface;
use App\Domains\Finance\Interfaces\ChartOfAccountServiceInterface;
use App\Domains\Finance\Models\ChartOfAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ChartOfAccountService implements ChartOfAccountServiceInterface
{
    public function __construct(
        private readonly ChartOfAccountRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): ChartOfAccount
    {
        $account = $this->repository->findById($id, $organizationId);
        if (! $account) {
            throw new NotFoundException('Chart of Account not found.');
        }
        return $account;
    }

    public function create(array $data): ChartOfAccount
    {
        return DB::transaction(fn (): ChartOfAccount => $this->repository->create($data));
    }

    public function update(string $id, array $data, string $organizationId): ChartOfAccount
    {
        $account = $this->findById($id, $organizationId);
        return DB::transaction(fn (): ChartOfAccount => $this->repository->update($account, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $account = $this->findById($id, $organizationId);
        return $this->repository->delete($account);
    }
}
