<?php

declare(strict_types=1);

namespace App\Domains\Branch\Repositories;

use App\Core\Base\BaseRepository;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Branch\Interfaces\BranchRepositoryInterface;
use App\Domains\Branch\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * BranchRepository
 *
 * Concrete implementation of BranchRepositoryInterface.
 * Communicates exclusively with the database via Eloquent.
 * All list queries are scoped to organization_id for multi-tenant safety.
 *
 * Layer rule: No business logic. DB queries only.
 */
class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    /**
     * Columns searchable via ILIKE (PostgreSQL case-insensitive).
     *
     * @var array<string>
     */
    protected array $searchable = [
        'branch_code',
        'branch_name',
        'city',
        'email',
        'phone',
    ];

    /**
     * Whitelisted filter columns.
     * organization_id is always included for multi-tenant safety.
     *
     * @var array<string>
     */
    protected array $filterable = [
        'organization_id',
        'branch_type',
        'city',
        'province',
        'country',
        'status',
    ];

    /**
     * Whitelisted sort columns.
     *
     * @var array<string>
     */
    protected array $sortable = [
        'branch_name',
        'branch_code',
        'city',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * Inject the Branch model.
     */
    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Get all branches ordered by name.
     * For multi-branch systems, prefer findByOrganization() over all().
     *
     * @param  array<string>          $columns
     * @return Collection<int, Branch>
     */
    public function all(array $columns = ['*']): Collection
    {
        /** @var Collection<int, Branch> */
        return $this->model
            ->select($columns)
            ->orderBy('branch_name')
            ->get();
    }

    /**
     * Paginate branches with optional filters, search, and sort.
     * Defaults to branch_name ascending — natural order for clinic lists.
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
        string  $sortBy   = 'branch_name',
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
     * Find a branch by primary key.
     * Returns null when not found.
     */
    public function findById(string $id): ?Branch
    {
        /** @var Branch|null */
        return $this->model->find($id);
    }

    /**
     * Find a branch by UUID or throw NotFoundException.
     *
     * @throws NotFoundException
     */
    public function findByUuid(string $uuid): Branch
    {
        /** @var Branch|null $branch */
        $branch = $this->model->find($uuid);

        if ($branch === null) {
            throw new NotFoundException("Branch with ID [{$uuid}] not found.");
        }

        return $branch;
    }

    /**
     * Create a new branch record.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): Branch
    {
        /** @var Branch */
        return $this->model->create($data);
    }

    /**
     * Update a branch record by primary key.
     *
     * @param  array<string, mixed> $data
     * @throws NotFoundException
     */
    public function update(string $id, array $data): Branch
    {
        $branch = $this->findByUuid($id);
        $branch->update($data);

        return $branch->fresh() ?? $branch;
    }

    /**
     * Soft delete a branch by primary key.
     *
     * @throws NotFoundException
     */
    public function delete(string $id): bool
    {
        $branch = $this->findByUuid($id);

        return (bool) $branch->delete();
    }

    /**
     * Restore a soft-deleted branch by primary key.
     *
     * @throws NotFoundException
     */
    public function restore(string $id): bool
    {
        /** @var Branch|null $branch */
        $branch = $this->model->withTrashed()->find($id);

        if ($branch === null) {
            throw new NotFoundException("Branch with ID [{$id}] not found.");
        }

        return (bool) $branch->restore();
    }

    // -------------------------------------------------------------------------
    // Multi-tenant Queries
    // -------------------------------------------------------------------------

    /**
     * Get all branches belonging to a specific organization.
     * Ordered by branch_name ascending.
     *
     * @param  array<string>          $columns
     * @return Collection<int, Branch>
     */
    public function findByOrganization(
        string $organizationId,
        array  $columns = ['*'],
    ): Collection {
        /** @var Collection<int, Branch> */
        return $this->model
            ->select($columns)
            ->where('organization_id', $organizationId)
            ->orderBy('branch_name')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    /**
     * Search branches by keyword within a specific organization.
     * Matches against branch_code, branch_name, city, email, and phone.
     * Always scoped to organization_id for multi-tenant safety.
     */
    public function search(
        string $organizationId,
        string $keyword,
        int    $perPage = 15,
    ): LengthAwarePaginator {
        $query = $this->model->where('organization_id', $organizationId);
        $query = $this->applySearchQuery($query, $keyword);

        return $query->orderBy('branch_name')->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Delete Guard Queries
    // Pure existence checks — business decisions belong in Service.
    // -------------------------------------------------------------------------

    /**
     * Check whether the branch has any assigned users.
     */
    public function hasUsers(string $branchId): bool
    {
        return $this->hasRelation($branchId, 'users');
    }

    /**
     * Check whether the branch has any registered patients.
     */
    public function hasPatients(string $branchId): bool
    {
        return $this->hasRelation($branchId, 'patients');
    }

    /**
     * Check whether the branch has any appointments.
     */
    public function hasAppointments(string $branchId): bool
    {
        return $this->hasRelation($branchId, 'appointments');
    }

    /**
     * Check whether the branch has any inventory records.
     */
    public function hasInventories(string $branchId): bool
    {
        return $this->hasRelation($branchId, 'inventories');
    }

    /**
     * Check whether the branch has any finance transactions.
     */
    public function hasFinanceTransactions(string $branchId): bool
    {
        return $this->hasRelation($branchId, 'financeTransactions');
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
     * Check whether a branch has related records via a named Eloquent relation.
     * Extracted to avoid duplication across all has*() methods.
     *
     * @param  string $branchId  UUID of the branch.
     * @param  string $relation  Eloquent relation name defined on Branch model.
     */
    private function hasRelation(string $branchId, string $relation): bool
    {
        return $this->model
            ->where('id', $branchId)
            ->whereHas($relation)
            ->exists();
    }
}
