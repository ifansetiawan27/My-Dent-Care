<?php

declare(strict_types=1);

namespace App\Domains\Finance\Interfaces;

use App\Core\Contracts\ServiceInterface;
use App\Domains\Finance\Models\ChartOfAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChartOfAccountServiceInterface extends ServiceInterface
{
    /**
     * Find a chart of account by ID scoped to organization.
     */
    public function findByIdWithOrganization(string $id, string $organizationId): ChartOfAccount;

    /**
     * Create a chart of account scoped to organization.
     */
    public function createForOrganization(array $data, string $organizationId): ChartOfAccount;

    /**
     * Update a chart of account scoped to organization.
     */
    public function updateForOrganization(string $id, array $data, string $organizationId): ChartOfAccount;

    /**
     * Delete a chart of account scoped to organization.
     */
    public function deleteForOrganization(string $id, string $organizationId): bool;
}
