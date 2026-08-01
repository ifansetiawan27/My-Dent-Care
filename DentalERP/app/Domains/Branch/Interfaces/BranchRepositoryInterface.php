<?php

declare(strict_types=1);

namespace App\Domains\Branch\Interfaces;

use App\Core\Contracts\RepositoryInterface;
use App\Domains\Branch\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BranchRepositoryInterface extends RepositoryInterface
{
    // -------------------------------------------------------------------------
    // Multi-tenant Queries
    // Every list query must be scoped to organization_id per AGENTS.md.
    // -------------------------------------------------------------------------

    /**
     * Get all branches belonging to a specific organization.
     *
     * @param  string            $organizationId
     * @param  array<string>     $columns
     * @return Collection<int, Branch>
     */
    public function findByOrganization(
        string $organizationId,
        array  $columns = ['*'],
    ): Collection;

    /**
     * Paginate branches scoped to a specific organization.
     *
     * @param  string               $organizationId
     * @param  int                  $perPage
     * @param  array<string, mixed> $filters
     * @param  string|null          $search
     * @param  string               $sortBy
     * @param  string               $sortDir
     * @return LengthAwarePaginator
     */
    public function paginateByOrganization(
        string  $organizationId,
        int     $perPage  = 15,
        array   $filters  = [],
        ?string $search   = null,
        string  $sortBy   = 'branch_name',
        string  $sortDir  = 'asc',
    ): LengthAwarePaginator;

    // -------------------------------------------------------------------------
    // Lookup Queries
    // -------------------------------------------------------------------------

    /**
     * Find a branch by its composite unique key (organization + branch_code).
     *
     * @param  string       $organizationId
     * @param  string       $branchCode
     * @return Branch|null
     */
    public function findByCode(
        string $organizationId,
        string $branchCode,
    ): ?Branch;

    /**
     * Check whether a branch code already exists within a given organization.
     * Used to enforce unique branch_code per organization.
     *
     * @param  string      $organizationId
     * @param  string      $branchCode
     * @param  string|null $excludeId  Exclude a specific branch ID (useful on update).
     * @return bool
     */
    public function existsByCode(
        string  $organizationId,
        string  $branchCode,
        ?string $excludeId = null,
    ): bool;

    // -------------------------------------------------------------------------
    // Delete Guard Queries
    // These methods support the Service layer in enforcing business delete rules.
    // Repository queries only — no business decisions made here.
    // -------------------------------------------------------------------------

    /**
     * Check whether the branch has any assigned users.
     *
     * @param  string $branchId
     * @return bool
     */
    public function hasUsers(string $branchId): bool;

    /**
     * Check whether the branch has any registered patients.
     *
     * @param  string $branchId
     * @return bool
     */
    public function hasPatients(string $branchId): bool;

    /**
     * Check whether the branch has any appointments.
     *
     * @param  string $branchId
     * @return bool
     */
    public function hasAppointments(string $branchId): bool;

    /**
     * Check whether the branch has any inventory records.
     *
     * @param  string $branchId
     * @return bool
     */
    public function hasInventories(string $branchId): bool;

    /**
     * Check whether the branch has any finance transactions.
     *
     * @param  string $branchId
     * @return bool
     */
    public function hasFinanceTransactions(string $branchId): bool;
}
