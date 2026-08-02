<?php

declare(strict_types=1);

namespace App\Domains\User\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\User\DTO\ChangePasswordDTO;
use App\Domains\User\DTO\CreateUserDTO;
use App\Domains\User\DTO\ResetPasswordDTO;
use App\Domains\User\DTO\UpdateProfileDTO;
use App\Domains\User\DTO\UpdateUserDTO;
use App\Domains\User\Interfaces\UserRepositoryInterface;
use App\Domains\User\Interfaces\UserServiceInterface;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * UserService
 *
 * Implements all business operations for the User domain.
 * Enforces business rules, wraps writes in DB transactions,
 * and delegates all data access to UserRepositoryInterface.
 *
 * Layer rule: No direct DB queries. All data access via repository.
 */
class UserService implements UserServiceInterface
{
    /**
     * Service name used in structured log messages.
     */
    private const SERVICE_NAME = 'UserService';

    /**
     * Role name for super administrator.
     * Requires Spatie Laravel Permission package.
     */
    private const SUPER_ADMIN_ROLE = 'super_admin';

    /**
     * Inject the User repository interface.
     */
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    // -------------------------------------------------------------------------
    // Write Operations — Account Management (Admin)
    // -------------------------------------------------------------------------

    /**
     * Create a new user account inside a database transaction.
     *
     * @throws BusinessException  If username, email, or employee_code is already taken.
     * @throws NotFoundException  When referenced org or branch does not exist.
     */
    public function create(CreateUserDTO $dto): User
    {
        try {
            return DB::transaction(function () use ($dto): User {

                // Rule: username must be globally unique
                if ($this->isUsernameTaken($dto->username)) {
                    throw new BusinessException(
                        "Username [{$dto->username}] is already taken.",
                        context: ['username' => $dto->username],
                    );
                }

                // Rule: email must be globally unique
                if ($this->isEmailTaken($dto->email)) {
                    throw new BusinessException(
                        "Email [{$dto->email}] is already registered.",
                        context: ['email' => $dto->email],
                    );
                }

                // Rule: employee_code must be globally unique
                if ($this->isEmployeeCodeTaken($dto->employeeCode)) {
                    throw new BusinessException(
                        "Employee code [{$dto->employeeCode}] is already in use.",
                        context: ['employee_code' => $dto->employeeCode],
                    );
                }

                $user = $this->repository->create($dto->toArray());

                $this->logInfo('create', 'User account created.', [
                    'id'            => $user->id,
                    'username'      => $user->username,
                    'employee_code' => $user->employee_code,
                    'organization'  => $user->organization_id,
                    'branch'        => $user->branch_id,
                ]);

                return $user;
            });
        } catch (BusinessException $e) {
            $this->logWarning('create', $e->getMessage(), $e->getContext());
            throw $e;
        } catch (Throwable $e) {
            $this->logError('create', $e, ['username' => $dto->username]);
            throw $e;
        }
    }

    /**
     * Update an existing user account inside a database transaction.
     * Admin-level operation.
     *
     * @throws NotFoundException  If the user does not exist.
     * @throws BusinessException  If uniqueness rules are violated.
     */
    public function update(string $id, UpdateUserDTO $dto): User
    {
        try {
            return DB::transaction(function () use ($id, $dto): User {
                $user = $this->repository->findByUuid($id);

                // Rule: username must remain globally unique when changed
                if ($dto->username !== null && $dto->username !== $user->username) {
                    if ($this->isUsernameTaken($dto->username, $id)) {
                        throw new BusinessException(
                            "Username [{$dto->username}] is already taken.",
                            context: ['username' => $dto->username],
                        );
                    }
                }

                // Rule: email must remain globally unique when changed
                if ($dto->email !== null && $dto->email !== $user->email) {
                    if ($this->isEmailTaken($dto->email, $id)) {
                        throw new BusinessException(
                            "Email [{$dto->email}] is already registered.",
                            context: ['email' => $dto->email],
                        );
                    }
                }

                // Rule: employee_code must remain globally unique when changed
                if ($dto->employeeCode !== null && $dto->employeeCode !== $user->employee_code) {
                    if ($this->isEmployeeCodeTaken($dto->employeeCode, $id)) {
                        throw new BusinessException(
                            "Employee code [{$dto->employeeCode}] is already in use.",
                            context: ['employee_code' => $dto->employeeCode],
                        );
                    }
                }

                $updated = $this->repository->update($id, $dto->toArray());

                $this->logInfo('update', 'User account updated.', ['id' => $id]);

                return $updated;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('update', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('update', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Soft delete a user account inside a database transaction.
     *
     * @throws NotFoundException  If the user does not exist.
     * @throws BusinessException  If super admin or delete guards prevent deletion.
     */
    public function delete(string $id): bool
    {
        try {
            return DB::transaction(function () use ($id): bool {
                $user = $this->repository->findByUuid($id);

                // Rule: super admin cannot be deleted
                if ($this->isSuperAdmin($user)) {
                    throw new BusinessException(
                        'Super admin account cannot be deleted.',
                        context: ['id' => $id],
                    );
                }

                // Enforce delete guards
                $this->assertDeletable($id);

                $result = $this->repository->delete($id);

                $this->logInfo('delete', 'User account deleted.', [
                    'id'       => $id,
                    'username' => $user->username,
                ]);

                return $result;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('delete', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('delete', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted user account inside a database transaction.
     *
     * @throws NotFoundException  If the user does not exist.
     */
    public function restore(string $id): bool
    {
        try {
            return DB::transaction(function () use ($id): bool {
                $result = $this->repository->restore($id);

                $this->logInfo('restore', 'User account restored.', ['id' => $id]);

                return $result;
            });
        } catch (NotFoundException $e) {
            $this->logWarning('restore', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('restore', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Activate a user account inside a database transaction.
     *
     * @throws NotFoundException  If the user does not exist.
     */
    public function activate(string $id): User
    {
        try {
            return DB::transaction(function () use ($id): User {
                $user = $this->repository->activate($id);

                $this->logInfo('activate', 'User account activated.', [
                    'id'       => $id,
                    'username' => $user->username,
                ]);

                return $user;
            });
        } catch (NotFoundException $e) {
            $this->logWarning('activate', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('activate', $e, ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Deactivate a user account inside a database transaction.
     *
     * @throws NotFoundException  If the user does not exist.
     * @throws BusinessException  If the user tries to deactivate their own account.
     */
    public function deactivate(string $id): User
    {
        try {
            return DB::transaction(function () use ($id): User {

                // Rule: cannot deactivate own account
                if (Auth::id() === $id) {
                    throw new BusinessException(
                        'You cannot deactivate your own account.',
                        context: ['id' => $id],
                    );
                }

                $user = $this->repository->deactivate($id);

                $this->logInfo('deactivate', 'User account deactivated.', [
                    'id'           => $id,
                    'username'     => $user->username,
                    'deactivated_by' => Auth::id(),
                ]);

                return $user;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('deactivate', $e->getMessage(), ['id' => $id]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('deactivate', $e, ['id' => $id]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Password Management
    // -------------------------------------------------------------------------

    /**
     * Change the authenticated user's own password.
     *
     * @throws NotFoundException  If the user does not exist.
     * @throws BusinessException  If current password is wrong or new password matches old.
     */
    public function changePassword(string $userId, ChangePasswordDTO $dto): bool
    {
        try {
            return DB::transaction(function () use ($userId, $dto): bool {
                $user = $this->repository->findByUuid($userId);

                // Rule: current password must be correct
                if (! Hash::check($dto->currentPassword, $user->password)) {
                    throw new BusinessException(
                        'Current password is incorrect.',
                        context: ['user_id' => $userId],
                    );
                }

                // Rule: new password must differ from current
                if (Hash::check($dto->newPassword, $user->password)) {
                    throw new BusinessException(
                        'New password must be different from your current password.',
                        context: ['user_id' => $userId],
                    );
                }

                $result = $this->repository->changePassword($userId, $dto->newPassword);

                $this->logInfo('changePassword', 'User changed their own password.', [
                    'user_id' => $userId,
                ]);

                return $result;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('changePassword', $e->getMessage(), ['user_id' => $userId]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('changePassword', $e, ['user_id' => $userId]);
            throw $e;
        }
    }

    /**
     * Reset a user's password (admin operation — no current password required).
     *
     * @throws NotFoundException  If the user does not exist.
     */
    public function resetPassword(string $userId, ResetPasswordDTO $dto): bool
    {
        try {
            return DB::transaction(function () use ($userId, $dto): bool {
                $result = $this->repository->changePassword($userId, $dto->newPassword);

                $this->logInfo('resetPassword', 'Admin reset user password.', [
                    'user_id'  => $userId,
                    'reset_by' => Auth::id(),
                ]);

                return $result;
            });
        } catch (NotFoundException $e) {
            $this->logWarning('resetPassword', $e->getMessage(), ['user_id' => $userId]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('resetPassword', $e, ['user_id' => $userId]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Profile Operations — Self-service
    // -------------------------------------------------------------------------

    /**
     * Get the authenticated user's full profile with organization and branch.
     *
     * @throws NotFoundException  If the user does not exist.
     */
    public function getProfile(string $userId): User
    {
        try {
            $user = $this->repository->findByUuid($userId);
            $user->load(['organization', 'branch']);

            return $user;
        } catch (NotFoundException $e) {
            $this->logWarning('getProfile', $e->getMessage(), ['user_id' => $userId]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('getProfile', $e, ['user_id' => $userId]);
            throw $e;
        }
    }

    /**
     * Update the authenticated user's own profile (safe personal fields only).
     *
     * @throws NotFoundException  If the user does not exist.
     * @throws BusinessException  If the DTO has no fields to update.
     */
    public function updateProfile(string $userId, UpdateProfileDTO $dto): User
    {
        try {
            return DB::transaction(function () use ($userId, $dto): User {

                if ($dto->isEmpty()) {
                    throw new BusinessException(
                        'No profile fields provided to update.',
                        context: ['user_id' => $userId],
                    );
                }

                $updated = $this->repository->update($userId, $dto->toArray());

                $this->logInfo('updateProfile', 'User updated their own profile.', [
                    'user_id' => $userId,
                ]);

                return $updated;
            });
        } catch (NotFoundException | BusinessException $e) {
            $this->logWarning('updateProfile', $e->getMessage(), ['user_id' => $userId]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('updateProfile', $e, ['user_id' => $userId]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get a paginated list of users for a given organization.
     */
    public function paginate(
        string $organizationId,
        array  $params = [],
    ): LengthAwarePaginator {
        try {
            $filters = array_merge(
                (array) ($params['filters'] ?? []),
                ['organization_id' => $organizationId],
            );

            return $this->repository->paginate(
                perPage: (int) ($params['per_page'] ?? 15),
                filters: $filters,
                search:  $params['search']   ?? null,
                sortBy:  (string) ($params['sort_by']  ?? 'name'),
                sortDir: (string) ($params['sort_dir'] ?? 'asc'),
            );
        } catch (Throwable $e) {
            $this->logError('paginate', $e, ['organization_id' => $organizationId]);
            throw $e;
        }
    }

    /**
     * Search users by keyword within a specific organization.
     */
    public function search(
        string $organizationId,
        string $keyword,
        int    $perPage = 15,
    ): LengthAwarePaginator {
        try {
            return $this->repository->search($organizationId, $keyword, $perPage);
        } catch (Throwable $e) {
            $this->logError('search', $e, [
                'organization_id' => $organizationId,
                'keyword'         => $keyword,
            ]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Private — Business Rule Helpers
    // -------------------------------------------------------------------------

    /**
     * Check whether a username is already taken.
     * Optionally excludes a user ID (for update self-comparison).
     */
    private function isUsernameTaken(string $username, ?string $excludeId = null): bool
    {
        $found = $this->repository->findByUsername($username);
        if ($found === null) return false;
        if ($excludeId !== null && $found->id === $excludeId) return false;
        return true;
    }

    /**
     * Check whether an email is already registered.
     * Optionally excludes a user ID (for update self-comparison).
     */
    private function isEmailTaken(string $email, ?string $excludeId = null): bool
    {
        $found = $this->repository->findByEmail($email);
        if ($found === null) return false;
        if ($excludeId !== null && $found->id === $excludeId) return false;
        return true;
    }

    /**
     * Check whether an employee code is already in use.
     * Optionally excludes a user ID (for update self-comparison).
     */
    private function isEmployeeCodeTaken(string $employeeCode, ?string $excludeId = null): bool
    {
        /** @var User|null $found */
        $found = $this->repository->findOneByField('employee_code', $employeeCode);
        if ($found === null) return false;
        if ($excludeId !== null && $found->id === $excludeId) return false;
        return true;
    }

    /**
     * Determine whether the user has the super admin role.
     * Requires Spatie Laravel Permission package.
     * Returns false gracefully if the method is not available.
     */
    private function isSuperAdmin(User $user): bool
    {
        if (! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole(self::SUPER_ADMIN_ROLE);
    }

    /**
     * Assert that a user satisfies all delete guard rules.
     * Throws BusinessException if any guard fails.
     *
     * @throws BusinessException
     */
    private function assertDeletable(string $userId): void
    {
        $guards = [
            'Appointments'         => fn (): bool => $this->repository->hasAppointments($userId),
            'Clinical Records'     => fn (): bool => $this->repository->hasClinicalRecords($userId),
            'Finance Transactions' => fn (): bool => $this->repository->hasFinanceTransactions($userId),
        ];

        foreach ($guards as $label => $check) {
            if ($check()) {
                throw new BusinessException(
                    "Cannot delete user. They still have {$label}.",
                    context: ['user_id' => $userId, 'guard' => $label],
                );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Private — Logging Helpers
    // -------------------------------------------------------------------------

    /**
     * Log an informational message with structured context.
     *
     * @param  array<string, mixed> $context
     */
    private function logInfo(string $action, string $message, array $context = []): void
    {
        Log::info(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
        );
    }

    /**
     * Log a warning message with structured context.
     *
     * @param  array<string, mixed> $context
     */
    private function logWarning(string $action, string $message, array $context = []): void
    {
        Log::warning(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
        );
    }

    /**
     * Log an error with full exception details.
     *
     * @param  array<string, mixed> $context
     */
    private function logError(string $action, Throwable $e, array $context = []): void
    {
        Log::error(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $e->getMessage(),
            [
                'service'   => self::SERVICE_NAME,
                'exception' => $e::class,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                ...$context,
            ],
        );
    }
}
