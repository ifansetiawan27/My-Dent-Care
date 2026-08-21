<?php

declare(strict_types=1);

namespace App\Domains\User\Repositories;

use App\Core\Base\BaseRepository;
use App\Core\Exceptions\NotFoundException;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Interfaces\UserRepositoryInterface;
use App\Domains\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UserRepository
 *
 * Concrete implementation of UserRepositoryInterface.
 * Communicates exclusively with the database via Eloquent.
 * All list queries are scoped to organization_id or branch_id for multi-tenant safety.
 *
 * Layer rule: No business logic. No validation. DB queries only.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Columns searchable via ILIKE (PostgreSQL case-insensitive).
     *
     * @var array<string>
     */
    protected array $searchable = [
        'name',
        'username',
        'email',
        'employee_code',
        'phone',
    ];

    /**
     * Whitelisted filter columns.
     * Always includes multi-tenant scope columns.
     *
     * @var array<string>
     */
    protected array $filterable = [
        'organization_id',
        'branch_id',
        'status',
        'gender',
    ];

    /**
     * Whitelisted sort columns.
     *
     * @var array<string>
     */
    protected array $sortable = [
        'name',
        'username',
        'email',
        'employee_code',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Inject the User model.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Get all users ordered by name.
     * In multi-tenant contexts, prefer findByOrganization() or findByBranch().
     *
     * @param  array<string>         $columns
     * @return Collection<int, User>
     */
    public function all(array $columns = ['*']): Collection
    {
        /** @var Collection<int, User> */
        return $this->model
            ->select($columns)
            ->orderBy('name')
            ->get();
    }

    /**
     * Paginate users with optional filters, search, and sort.
     * Defaults to name ascending — natural order for user lists.
     *
     * @param  int                  $perPage
     * @param  array<string, mixed> $filters
     * @param  string|null          $search
     * @param  string               $sortBy
     * @param  string               $sortDir
     * @param  array<string>        $columns
     */
    public function paginate(
        int     $perPage  = 15,
        array   $filters  = [],
        ?string $search   = null,
        string  $sortBy   = 'name',
        string  $sortDir  = 'asc',
        array   $columns  = ['*'],
    ): LengthAwarePaginator {
        $query = $this->model->select($columns);

        if ($search !== null && $search !== '') {
            $query = $this->applySearchQuery($query, $search);
        }

        if (! empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        $query = $this->applySort($query, $sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Find a user by primary key.
     * Returns null when not found.
     */
    public function findById(string $id): ?User
    {
        /** @var User|null */
        return $this->model->find($id);
    }

    /**
     * Find a user by UUID or throw NotFoundException.
     *
     * @throws NotFoundException
     */
    public function findByUuid(string $uuid): User
    {
        /** @var User|null $user */
        $user = $this->model->find($uuid);

        if ($user === null) {
            throw new NotFoundException("User with ID [{$uuid}] not found.");
        }

        return $user;
    }

    /**
     * Create a new user record.
     * Password must already be hashed — or pass plaintext and let hashed cast handle it.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): User
    {
        /** @var User */
        return $this->model->create($data);
    }

    /**
     * Update an existing user record by primary key.
     *
     * @param  array<string, mixed> $data
     * @throws NotFoundException
     */
    public function update(string $id, array $data): User
    {
        $user = $this->findByUuid($id);
        $user->update($data);

        return $user->fresh() ?? $user;
    }

    /**
     * Soft delete a user by primary key.
     *
     * @throws NotFoundException
     */
    public function delete(string $id): bool
    {
        $user = $this->findByUuid($id);

        return (bool) $user->delete();
    }

    /**
     * Restore a soft-deleted user by primary key.
     *
     * @throws NotFoundException
     */
    public function restore(string $id): bool
    {
        /** @var User|null $user */
        $user = $this->model->withTrashed()->find($id);

        if ($user === null) {
            throw new NotFoundException("User with ID [{$id}] not found.");
        }

        return (bool) $user->restore();
    }

    // -------------------------------------------------------------------------
    // Lookup Queries
    // -------------------------------------------------------------------------

    /**
     * Find a user by their unique username.
     * Returns null when not found.
     */
    public function findByUsername(string $username): ?User
    {
        /** @var User|null */
        return $this->model
            ->where('username', $username)
            ->first();
    }

    /**
     * Find a user by their unique email address.
     * Returns null when not found.
     */
    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->model
            ->where('email', $email)
            ->first();
    }

    // -------------------------------------------------------------------------
    // Multi-tenant Queries
    // -------------------------------------------------------------------------

    /**
     * Get all users belonging to a specific organization.
     * Ordered by name ascending.
     *
     * @param  array<string>         $columns
     * @return Collection<int, User>
     */
    public function findByOrganization(
        string $organizationId,
        array  $columns = ['*'],
    ): Collection {
        /** @var Collection<int, User> */
        return $this->model
            ->select($columns)
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all users assigned to a specific branch.
     * Ordered by name ascending.
     *
     * @param  array<string>         $columns
     * @return Collection<int, User>
     */
    public function findByBranch(
        string $branchId,
        array  $columns = ['*'],
    ): Collection {
        /** @var Collection<int, User> */
        return $this->model
            ->select($columns)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    /**
     * Search users by keyword within a specific organization.
     * Matches against name, username, email, employee_code, and phone.
     * Always scoped to organization_id for multi-tenant safety.
     */
    public function search(
        string $organizationId,
        string $keyword,
        int    $perPage = 15,
    ): LengthAwarePaginator {
        $query = $this->model->where('organization_id', $organizationId);
        $query = $this->applySearchQuery($query, $keyword);

        return $query->orderBy('name')->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Credential & Auth Operations
    // -------------------------------------------------------------------------

    /**
     * Update the user's password.
     * Laravel 12's 'hashed' cast detects pre-hashed values via Hash::isHashed()
     * and will not double-hash. Pass either plaintext or a bcrypt hash.
     *
     * @throws NotFoundException
     */
    public function changePassword(string $id, string $hashedPassword): bool
    {
        $user = $this->findByUuid($id);

        return (bool) $user->update(['password' => $hashedPassword]);
    }

    /**
     * Update the user's last_login_at to the current timestamp.
     * Called immediately after successful authentication.
     *
     * @throws NotFoundException
     */
    public function updateLastLogin(string $id): bool
    {
        $user = $this->findByUuid($id);

        return (bool) $user->update(['last_login_at' => now()]);
    }

    // -------------------------------------------------------------------------
    // Status Operations
    // -------------------------------------------------------------------------

    /**
     * Set the user's status to active.
     *
     * @throws NotFoundException
     */
    public function activate(string $id): User
    {
        $user = $this->findByUuid($id);
        $user->update(['status' => UserStatus::Active->value]);

        return $user->fresh() ?? $user;
    }

    /**
     * Set the user's status to inactive.
     * An inactive user cannot log in.
     *
     * @throws NotFoundException
     */
    public function deactivate(string $id): User
    {
        $user = $this->findByUuid($id);
        $user->update(['status' => UserStatus::Inactive->value]);

        return $user->fresh() ?? $user;
    }

    // -------------------------------------------------------------------------
    // Delete Guard Queries
    // Pure existence checks — business decisions belong in Service.
    // -------------------------------------------------------------------------

    /**
     * Check whether the user has any appointments as the assigned clinician.
     */
    public function hasAppointments(string $userId): bool
    {
        return $this->hasRecordsInTable('appointments', $userId);
    }

    /**
     * Check whether the user has any clinical (EMR) records.
     */
    public function hasClinicalRecords(string $userId): bool
    {
        return $this->hasRecordsInTable('medical_records', $userId);
    }

    /**
     * Check whether the user has any finance transactions.
     */
    public function hasFinanceTransactions(string $userId): bool
    {
        return $this->hasRecordsInTable('finance_transactions', $userId);
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Apply ILIKE search across all whitelisted $searchable columns.
     * Extracted to avoid duplication between paginate() and search().
     */
    private function applySearchQuery(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $q) use ($keyword): void {
            foreach ($this->searchable as $column) {
                $q->orWhere($column, 'ILIKE', "%{$keyword}%");
            }
        });
    }

    /**
     * Check whether a given table has non-deleted records linked to a user.
     * Extracted to remove duplication across all has*() guard methods.
     *
     * @param  string $table   Table name to query.
     * @param  string $userId  UUID of the user.
     */
    private function hasRecordsInTable(string $table, string $userId): bool
    {
        return DB::table($table)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->exists();
    }
}
