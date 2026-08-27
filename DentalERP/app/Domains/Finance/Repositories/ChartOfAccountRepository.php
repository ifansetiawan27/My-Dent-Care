<?php

declare(strict_types=1);

namespace App\Domains\Finance\Repositories;

use App\Core\Base\BaseRepository;
use App\Domains\Finance\Interfaces\ChartOfAccountRepositoryInterface;
use App\Domains\Finance\Models\ChartOfAccount;

class ChartOfAccountRepository extends BaseRepository implements ChartOfAccountRepositoryInterface
{
    public function __construct(ChartOfAccount $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code, string $organizationId): ?ChartOfAccount
    {
        return $this->model->where('account_code', $code)
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function findByType(string $type, string $organizationId): array
    {
        return $this->model->where('account_type', $type)
            ->where('organization_id', $organizationId)
            ->orderBy('account_code')
            ->get()->all();
    }

    public function findActiveByOrganization(string $organizationId): array
    {
        return $this->model->where('is_active', true)
            ->where('organization_id', $organizationId)
            ->orderBy('account_code')
            ->get()->all();
    }

    public function findHierarchy(string $organizationId): array
    {
        return $this->model->where('organization_id', $organizationId)
            ->orderBy('account_code')
            ->with('children')
            ->get()->all();
    }
}
