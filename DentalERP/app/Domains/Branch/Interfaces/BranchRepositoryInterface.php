<?php

declare(strict_types=1);

namespace App\Domains\Branch\Interfaces;

use App\Core\Contracts\RepositoryInterface;
use App\Domains\Branch\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * BranchRepositoryInterface
 *
 * Defines all data-access operations for the Branch domain.
 * Extends the Core RepositoryInterface (which provides generic CRUD via CrudInterface).
 * Adds Branch-specific typed methods and multi-tenant query contracts.
 *
 * Layer rule: Only database queries — no business logic allowed.
 */
interface BranchRepositoryInterface extends RepositoryInterface
{
    // -------------------------------------------------------------------------
    // CRUD — Typed return for Branch domain
    // Core RepositoryInterface returns Model (generic).
    // These methods return Branch (specific) for type safety.
    // -------------------------------------------------------------------------

    /**
     * Get all branches.
     * For multi-branch systems, prefer findByOrganization() over all().
     *
     * @param  array<string>          $columns
     * @return Collection<int, Branch>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Paginate branches with optional filters, search, and sort.
     *
     * @param  int                  $perPage
     * @param  array<string, mixed> $filters
     * @param  string|null          $search
     * @param  string               $sortBy
     * @param  string               $sortDir
     * @param  array<string>        $columns
     * @return LengthAwarePaginator
     */
    public function paginate(
        int     $perPage  = 15,
        array   $filters  = [],
        ?string $search   = null,
        string  $sortBy   = 'branch_name',
        string  $sortDir  = 'asc',
        array   $columns  = ['*'],
    ): LengthAwarePaginator;

    /**
     * Find a branch by primary key.
     * Returns null when not found.
     *
     * @param  string       $id  UUID of the branch.
     * @return Branch|null
     */
    public function findById(string $id): ?Branch;

    /**
     * Find a branch by UUID or throw NotFoundException.
     * Use this when the branch is expected to exist.
     *
     * @param  string $uuid  UUID of the branch.
     * @return Branch
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function findByUuid(string $uuid): Branch;

    /**
     * Create a new branch record.
     *
     * @param  array<string, mixed> $data
     * @return Branch
     */
    public function create(array $data): Branch;

    /**
     * Update a branch record by primary key.
     *
     * @param  string               $id
     * @param  array<string, mixed> $data
     * @return Branch
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function update(string $id, array $data): Branch;

    /**
     * Soft delete a branch by primary key.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function delete(string $id): bool;

    /**
     * Restore a soft-deleted branch by primary key.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function restore(string $id): bool;

    // -------------------------------------------------------------------------
    // Multi-tenant Queries
    // All list queries must be scoped to organization_id — AGENTS.md standard.
    // -------------------------------------------------------------------------

    /**
     * Get all branches belonging to a specific organization.
     *
     * @param  string             $organizationId
     * @param  array<string>      $columns
     * @return Collection<int, Branch>
     */
    public function findByOrganization(
        string $organizationId,
        array  $columns = ['*'],
    ): Collection;

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    /**
     * Search branches by keyword within a specific organization.
     * Matches against branch_code, branch_name, city, email, and phone.
     * Always scoped to organization_id for multi-tenant safety.
     *
     * @param  string $organizationId
     * @param  string $keyword
     * @param  int    $perPage
     * @return LengthAwarePaginator
     */
    public function search(
        string $organizationId,
        string $keyword,
        int    $perPage = 15,
    ): LengthAwarePaginator;

    // -------------------------------------------------------------------------
    // Delete Guard Queries
    // Pure existence checks — business decisions belong in Service.
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
