<?php

declare(strict_types=1);

namespace App\Domains\User\Interfaces;

use App\Core\Contracts\RepositoryInterface;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * UserRepositoryInterface
 *
 * Defines all data-access operations for the User domain.
 * Extends the Core RepositoryInterface for generic CRUD.
 * Adds User-specific typed methods for auth, profile, and multi-tenant queries.
 *
 * Layer rule: Only database queries — no business logic allowed.
 * Password hashing, permission checks, and auth decisions belong in Service.
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    // -------------------------------------------------------------------------
    // CRUD — Typed returns for the User domain
    // Core RepositoryInterface returns Model (generic).
    // These methods return User (specific) for type safety.
    // -------------------------------------------------------------------------

    /**
     * Get all users.
     * In multi-tenant contexts, prefer findByOrganization() or findByBranch().
     *
     * @param  array<string>         $columns
     * @return Collection<int, User>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Paginate users with optional filters, search, and sort.
     * Always scope filters to organization_id or branch_id.
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
        string  $sortBy   = 'name',
        string  $sortDir  = 'asc',
        array   $columns  = ['*'],
    ): LengthAwarePaginator;

    /**
     * Find a user by primary key.
     * Returns null when not found.
     *
     * @param  string    $id  UUID of the user.
     * @return User|null
     */
    public function findById(string $id): ?User;

    /**
     * Find a user by UUID or throw NotFoundException.
     * Use this when the user is expected to exist.
     *
     * @param  string $uuid  UUID of the user.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function findByUuid(string $uuid): User;

    /**
     * Create a new user record.
     * Password must already be hashed before passing to this method.
     *
     * @param  array<string, mixed> $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Update an existing user record by primary key.
     *
     * @param  string               $id
     * @param  array<string, mixed> $data
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function update(string $id, array $data): User;

    /**
     * Soft delete a user by primary key.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function delete(string $id): bool;

    /**
     * Restore a soft-deleted user by primary key.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function restore(string $id): bool;

    // -------------------------------------------------------------------------
    // Lookup Queries — by unique identifiers
    // -------------------------------------------------------------------------

    /**
     * Find a user by their unique username.
     * Returns null when not found.
     * Used during login and duplicate-check before registration.
     *
     * @param  string    $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User;

    /**
     * Find a user by their unique email address.
     * Returns null when not found.
     * Used during login, password reset, and duplicate-check.
     *
     * @param  string    $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    // -------------------------------------------------------------------------
    // Multi-tenant Queries
    // All list queries must be scoped to organization or branch — AGENTS.md standard.
    // -------------------------------------------------------------------------

    /**
     * Get all users belonging to a specific organization.
     *
     * @param  string             $organizationId
     * @param  array<string>      $columns
     * @return Collection<int, User>
     */
    public function findByOrganization(
        string $organizationId,
        array  $columns = ['*'],
    ): Collection;

    /**
     * Get all users assigned to a specific branch.
     *
     * @param  string             $branchId
     * @param  array<string>      $columns
     * @return Collection<int, User>
     */
    public function findByBranch(
        string $branchId,
        array  $columns = ['*'],
    ): Collection;

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    /**
     * Search users by keyword within a specific organization.
     * Matches against name, username, email, employee_code, and phone.
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
    // Credential & Auth Operations
    // Repository updates data only — no hashing or auth logic here.
    // -------------------------------------------------------------------------

    /**
     * Update the user's password.
     * The password MUST already be hashed by the Service layer before calling this.
     *
     * @param  string $id             UUID of the user.
     * @param  string $hashedPassword Bcrypt-hashed password string.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function changePassword(string $id, string $hashedPassword): bool;

    /**
     * Update the user's last_login_at to the current timestamp.
     * Called by the authentication flow immediately after successful login.
     *
     * @param  string $id  UUID of the user.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function updateLastLogin(string $id): bool;

    // -------------------------------------------------------------------------
    // Status Operations
    // -------------------------------------------------------------------------

    /**
     * Set the user's status to active.
     * Returns the updated User model.
     *
     * @param  string $id  UUID of the user.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function activate(string $id): User;

    /**
     * Set the user's status to inactive.
     * An inactive user cannot log in.
     * Returns the updated User model.
     *
     * @param  string $id  UUID of the user.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function deactivate(string $id): User;

    // -------------------------------------------------------------------------
    // Delete Guard Queries
    // Pure existence checks — business decisions belong in Service.
    // -------------------------------------------------------------------------

    /**
     * Check whether the user has any appointments as the assigned clinician.
     *
     * @param  string $userId
     * @return bool
     */
    public function hasAppointments(string $userId): bool;

    /**
     * Check whether the user has any clinical (EMR) records.
     *
     * @param  string $userId
     * @return bool
     */
    public function hasClinicalRecords(string $userId): bool;

    /**
     * Check whether the user has any finance transactions.
     *
     * @param  string $userId
     * @return bool
     */
    public function hasFinanceTransactions(string $userId): bool;
}
