<?php

declare(strict_types=1);

namespace App\Domains\User\Interfaces;

use App\Domains\User\DTO\ChangePasswordDTO;
use App\Domains\User\DTO\CreateUserDTO;
use App\Domains\User\DTO\ResetPasswordDTO;
use App\Domains\User\DTO\UpdateProfileDTO;
use App\Domains\User\DTO\UpdateUserDTO;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * UserServiceInterface
 *
 * Defines all business operation contracts for the User domain.
 * Implementations enforce business rules, wrap writes in transactions,
 * and delegate all data access to UserRepositoryInterface.
 *
 * Layer rule: Business logic only — no direct DB queries allowed.
 */
interface UserServiceInterface
{
    // -------------------------------------------------------------------------
    // Write Operations — Account Management (Admin)
    // All write operations must be wrapped in a database transaction.
    // Business rules are enforced here before delegating to the repository.
    // -------------------------------------------------------------------------

    /**
     * Create a new user account inside a database transaction.
     *
     * Business Rules enforced:
     * - Organization must exist and be active.
     * - Branch must belong to the same organization.
     * - Username must be globally unique.
     * - Email must be globally unique.
     * - Employee code must be globally unique.
     *
     * @param  CreateUserDTO $dto  Validated creation data.
     * @return User
     *
     * @throws \App\Core\Exceptions\BusinessException  If any business rule is violated.
     * @throws \App\Core\Exceptions\NotFoundException  If organization or branch does not exist.
     */
    public function create(CreateUserDTO $dto): User;

    /**
     * Update an existing user account inside a database transaction.
     * Admin-level operation — can change branch, employee_code, status, etc.
     *
     * Business Rules enforced:
     * - Username must remain globally unique when changed.
     * - Email must remain globally unique when changed.
     * - Employee code must remain globally unique when changed.
     * - If branch_id changes, the new branch must belong to the same organization.
     *
     * @param  string        $id   UUID of the user to update.
     * @param  UpdateUserDTO $dto  Validated update data.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     * @throws \App\Core\Exceptions\BusinessException  If any business rule is violated.
     */
    public function update(string $id, UpdateUserDTO $dto): User;

    /**
     * Soft delete a user account inside a database transaction.
     *
     * Business Rules enforced (Delete Guards):
     * - Cannot delete if user has active Appointments.
     * - Cannot delete if user has clinical (EMR) records.
     * - Cannot delete if user has Finance Transactions.
     *
     * @param  string $id  UUID of the user to delete.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     * @throws \App\Core\Exceptions\BusinessException  If delete guards are not satisfied.
     */
    public function delete(string $id): bool;

    /**
     * Restore a soft-deleted user account inside a database transaction.
     *
     * @param  string $id  UUID of the user to restore.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     */
    public function restore(string $id): bool;

    /**
     * Activate a user account inside a database transaction.
     * Sets status to active — allows the user to log in.
     *
     * @param  string $id  UUID of the user to activate.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     */
    public function activate(string $id): User;

    /**
     * Deactivate a user account inside a database transaction.
     * Sets status to inactive — prevents the user from logging in.
     *
     * @param  string $id  UUID of the user to deactivate.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     */
    public function deactivate(string $id): User;

    // -------------------------------------------------------------------------
    // Write Operations — Password Management
    // -------------------------------------------------------------------------

    /**
     * Change the authenticated user's own password inside a database transaction.
     *
     * Business Rules enforced:
     * - Current password must match the stored hash.
     * - New password must differ from the current password.
     *
     * @param  string            $userId  UUID of the authenticated user.
     * @param  ChangePasswordDTO $dto     Contains current and new plaintext passwords.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     * @throws \App\Core\Exceptions\BusinessException  If current password is incorrect.
     */
    public function changePassword(string $userId, ChangePasswordDTO $dto): bool;

    /**
     * Reset a user's password inside a database transaction (admin operation).
     * Does NOT require the current password — admin privilege only.
     *
     * @param  string           $userId  UUID of the target user.
     * @param  ResetPasswordDTO $dto     Contains the new plaintext password.
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     */
    public function resetPassword(string $userId, ResetPasswordDTO $dto): bool;

    // -------------------------------------------------------------------------
    // Profile Operations — Self-service
    // -------------------------------------------------------------------------

    /**
     * Get the authenticated user's full profile.
     * Includes related organization and branch data.
     *
     * @param  string $userId  UUID of the authenticated user.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     */
    public function getProfile(string $userId): User;

    /**
     * Update the authenticated user's own profile inside a database transaction.
     * Restricted to safe personal fields only — no account or role changes.
     *
     * @param  string           $userId  UUID of the authenticated user.
     * @param  UpdateProfileDTO $dto     Validated profile data.
     * @return User
     *
     * @throws \App\Core\Exceptions\NotFoundException  If the user does not exist.
     * @throws \App\Core\Exceptions\BusinessException  If any business rule is violated.
     */
    public function updateProfile(string $userId, UpdateProfileDTO $dto): User;

    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get a paginated list of users for a given organization.
     * Supports optional search, filter, and sort via $params.
     *
     * Accepted $params keys:
     *   per_page   int     Number of items per page (default: 15).
     *   search     string  Keyword to search across user fields.
     *   sort_by    string  Column to sort by (default: name).
     *   sort_dir   string  Sort direction: asc|desc (default: asc).
     *   filters    array   Key-value column filters (status, gender, branch_id).
     *
     * @param  string               $organizationId  UUID of the organization.
     * @param  array<string, mixed> $params          Query parameters.
     * @return LengthAwarePaginator
     */
    public function paginate(
        string $organizationId,
        array  $params = [],
    ): LengthAwarePaginator;

    /**
     * Search users by keyword within a specific organization.
     * Matches against name, username, email, employee_code, and phone.
     * Always scoped to organization_id for multi-tenant safety.
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
}
