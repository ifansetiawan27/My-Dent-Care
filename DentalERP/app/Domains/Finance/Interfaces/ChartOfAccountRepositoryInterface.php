<?php

declare(strict_types=1);

namespace App\Domains\Finance\Interfaces;

use App\Core\Contracts\RepositoryInterface;
use App\Domains\Finance\Models\ChartOfAccount;

interface ChartOfAccountRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $code, string $organizationId): ?ChartOfAccount;
    public function findByType(string $type, string $organizationId): array;
    public function findActiveByOrganization(string $organizationId): array;
    public function findHierarchy(string $organizationId): array;
}
