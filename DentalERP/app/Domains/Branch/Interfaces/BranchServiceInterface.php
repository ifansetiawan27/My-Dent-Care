<?php

declare(strict_types=1);

namespace App\Domains\Branch\Interfaces;

use App\Domains\Branch\DTO\CreateBranchDTO;
use App\Domains\Branch\DTO\UpdateBranchDTO;
use App\Domains\Branch\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * BranchServiceInterface
 *
 * Defines all business operation contracts for the Branch domain.
 * Implementations enforce business rules, wrap writes in transactions,
 * and delegate data access exclusively to BranchRepositoryInterface.
 *
 * Layer rule: Business logic only — no direct DB queries allowed.
 */
interface BranchServiceInterface
{
    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get all branches for a given organization.
     * Always scoped to organization_id for multi-tenant safety.
     *
     * @param  string             $organizationId  UUID of the organization.
     * @return Collection<int, Branch>
     */
    public function getAll(string $organizationId): Collection;

    /**
     * Get a paginated list of branches for a given organization.
     * Supports optional search, filter, and sort via $params.
     *
     * Accepted $params keys:
     *   per_page   int     Number of items per page (default: 15).
     *   search     string  Keyword to search across branch fields.
     *   sort_by    string  Column to sort by (default: branch_name).
     *   sort_dir   string  Sort direction: asc|desc (default: asc).
     *   filters    array   Key-value column filters.
     *
     * @param  string               $organizationId  UUID of the organization.
     * @param  array<string, mixed> $params          Query parameters.
     * @return LengthAwarePaginator
     */
    public function getPaginated(
        string $organizationId,
        array  $params = [],
    ): LengthAwarePaginator;

    /**
     * Find a branch by primary key.
     * Returns null when the branch does not exist.
     *
     * @param  string      $id  UUID of the branch.
     * @return Branch|null
     */
    public function getById(string $id): ?Branch;

    /**
     * Find a branch by UUID or throw NotFoundException.
     * Use this when the branch is expected to exist.
     *
     * @param  string $uuid  UUID of the branch.
     * @return Branch
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function getByUuid(string $uuid): Branch;

    /**
     * Get all branches belonging to a specific organization.
     * Returns a flat collection — use getPaginated() for paginated results.
     *
     * @param  string             $organizationId  UUID of the organization.
     * @return Collection<int, Branch>
     */
    public function getByOrganization(string $organizationId): Collection;

    /**
     * Search branches by keyword within a specific organization.
     * Matches against branch_code, branch_name, city, email, and phone.
     *
     * @param  string $organizationId  UUID of the organization.
     * @param  string $keyword         Search keyword.
     * @param  int    $perPage         Results per page (default: 15).
     * @return LengthAwarePaginator
     */
    public function search(
        string $organizationId,
        string $keyword,
        int    $perPage = 15,
    ): LengthAwarePaginator;

    // -------------------------------------------------------------------------
    // Write Operations
    // All write operations must be wrapped in a database transaction.
    // Business rules are enforced here before delegating to the repository.
    // -------------------------------------------------------------------------

    /**
     * Create a new branch inside a database transaction.
     * Enforces: organization must be active, branch_code unique within org.
     *
     * @param  CreateBranchDTO $dto  Validated creation data.
     * @return Branch
     *
     * @throws \App\Core\Exceptions\BusinessException  If business rules are violated.
     * @throws \App\Core\Exceptions\NotFoundException  If the organization does not exist.
     */
    public function create(CreateBranchDTO $dto): Branch;

    /**
     * Update an existing branch inside a database transaction.
     * Enforces: branch_code uniqueness within org when changed.
     *
     * @param  string          $id   UUID of the branch to update.
     * @param  UpdateBranchDTO $dto  Validated update data.
     * @return Branch
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the branch does not exist.
     * @throws \App\Core\Exceptions\BusinessException  If business rules are violated.
     */
    public function update(string $id, UpdateBranchDTO $dto): Branch;

    /**
     * Soft delete a branch inside a database transaction.
     * Enforces delete guards: branch must have no Users, Patients,
     * Appointments, Inventories, or Finance Transactions.
     *
     * @param  string $id  UUID of the branch to delete.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the branch does not exist.
     * @throws \App\Core\Exceptions\BusinessException  If delete guards are not satisfied.
     */
    public function delete(string $id): bool;

    /**
     * Restore a soft-deleted branch inside a database transaction.
     *
     * @param  string $id  UUID of the branch to restore.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the branch does not exist.
     */
    public function restore(string $id): bool;
}
