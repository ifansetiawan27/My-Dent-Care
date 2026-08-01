<?php

declare(strict_types=1);

namespace App\Domains\Branch\Repositories;

use App\Core\Base\BaseRepository;
use App\Domains\Branch\Interfaces\BranchRepositoryInterface;
use App\Domains\Branch\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    /**
     * Columns searchable via ILIKE — scoped to organization in paginateByOrganization.
     *
     * @var array<string>
     */
    protected array $searchable = [
        'branch_code',
        'branch_name',
        'city',
        'phone',
        'email',
    ];

    /**
     * Whitelisted filter columns.
     * Always includes organization_id for multi-tenant safety.
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
    // Multi-tenant Queries
    // -------------------------------------------------------------------------

    /**
     * Get all branches belonging to a specific organization.
     *
     * @param  array<string>     $columns
     * @return Collection<int, Branch>
     */
    public function findByOrganization(
        string $organizationId,
        array  $columns = ['*'],
    ): Collection {
        return $this->model
            ->select($columns)
            ->where('organization_id', $organizationId)
            ->orderBy('branch_name')
            ->get();
    }

    /**
     * Paginate branches scoped to a specific organization,
     * with optional search, filter, and sort.
     */
    public function paginateByOrganization(
        string  $organizationId,
        int     $perPage  = 15,
        array   $filters  = [],
        ?string $search   = null,
        string  $sortBy   = 'branch_name',
        string  $sortDir  = 'asc',
    ): LengthAwarePaginator {
        $query = $this->model
            ->where('organization_id', $organizationId);

        // Apply search across whitelisted searchable columns
        if ($search !== null && $search !== '' && ! empty($this->searchable)) {
            $query->where(function ($q) use ($search): void {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'ILIKE', "%{$search}%");
                }
            });
        }

        // Apply whitelisted filters (organization_id excluded — already applied above)
        if (! empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        // Apply whitelisted sort
        $query = $this->applySort($query, $sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // Lookup Queries
    // -------------------------------------------------------------------------

    /**
     * Find a branch by its composite unique key (organization_id + branch_code).
     */
    public function findByCode(
        string $organizationId,
        string $branchCode,
    ): ?Branch {
        /** @var Branch|null */
        return $this->model
            ->where('organization_id', $organizationId)
            ->where('branch_code', $branchCode)
            ->first();
    }

    /**
     * Check whether a branch_code already exists within a given organization.
     * Optionally excludes a branch ID — useful during update to ignore self.
     */
    public function existsByCode(
        string  $organizationId,
        string  $branchCode,
        ?string $excludeId = null,
    ): bool {
        $query = $this->model
            ->where('organization_id', $organizationId)
            ->where('branch_code', $branchCode);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    // -------------------------------------------------------------------------
    // Delete Guard Queries
    // Pure DB existence checks — business decisions belong in Service.
    // -------------------------------------------------------------------------

    /**
     * Check whether the branch has any assigned users.
     */
    public function hasUsers(string $branchId): bool
    {
        return $this->model
            ->where('id', $branchId)
            ->whereHas('users')
            ->exists();
    }

    /**
     * Check whether the branch has any registered patients.
     */
    public function hasPatients(string $branchId): bool
    {
        return $this->model
            ->where('id', $branchId)
            ->whereHas('patients')
            ->exists();
    }

    /**
     * Check whether the branch has any appointments.
     */
    public function hasAppointments(string $branchId): bool
    {
        return $this->model
            ->where('id', $branchId)
            ->whereHas('appointments')
            ->exists();
    }

    /**
     * Check whether the branch has any inventory records.
     */
    public function hasInventories(string $branchId): bool
    {
        return $this->model
            ->where('id', $branchId)
            ->whereHas('inventories')
            ->exists();
    }

    /**
     * Check whether the branch has any finance transactions.
     */
    public function hasFinanceTransactions(string $branchId): bool
    {
        return $this->model
            ->where('id', $branchId)
            ->whereHas('financeTransactions')
            ->exists();
    }
}
