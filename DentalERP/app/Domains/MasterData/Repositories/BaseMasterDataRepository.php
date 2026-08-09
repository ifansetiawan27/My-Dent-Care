<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Core\Exceptions\NotFoundException;
use App\Domains\MasterData\Interfaces\MasterDataRepositoryInterface;
use App\Domains\MasterData\Models\BaseMasterDataModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * BaseMasterDataRepository
 *
 * Generic Eloquent implementation for all Master Data reference repositories.
 * Handles all common queries: find, filter, search, paginate, activate/deactivate.
 *
 * Usage — extend for each reference table:
 *
 *   class CountryRepository extends BaseMasterDataRepository
 *   {
 *       public function __construct(Country $model)
 *       {
 *           parent::__construct($model);
 *       }
 *   }
 *
 * Layer rule: No business logic. DB queries only.
 */
abstract class BaseMasterDataRepository implements MasterDataRepositoryInterface
{
    /**
     * The model instance injected by the concrete repository.
     */
    protected BaseMasterDataModel $model;

    /**
     * Columns allowed for sorting.
     * Override in child repositories to add domain-specific sortable columns.
     *
     * @var array<string>
     */
    protected array $sortable = [
        'name',
        'code',
        'is_active',
        'created_at',
        'updated_at',
    ];

    /**
     * Inject the concrete model instance.
     */
    public function __construct(BaseMasterDataModel $model)
    {
        $this->model = $model;
    }

    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get all records, optionally filtered by active status.
     *
     * @param  array<string>                    $columns
     * @return Collection<int, BaseMasterDataModel>
     */
    public function findAll(bool $activeOnly = true, array $columns = ['*']): Collection
    {
        /** @var Collection<int, BaseMasterDataModel> */
        return $this->model
            ->select($columns)
            ->when($activeOnly, fn (Builder $q) => $q->active())
            ->ordered()
            ->get();
    }

    /**
     * Get active records for dropdown / select lists.
     * Returns minimal fields by default: id, code, name.
     *
     * @param  array<string>                    $columns
     * @return Collection<int, BaseMasterDataModel>
     */
    public function findActive(array $columns = ['id', 'code', 'name']): Collection
    {
        /** @var Collection<int, BaseMasterDataModel> */
        return $this->model
            ->select($columns)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Find a record by primary key.
     * Returns null when not found.
     */
    public function findById(string $id): ?BaseMasterDataModel
    {
        /** @var BaseMasterDataModel|null */
        return $this->model->find($id);
    }

    /**
     * Find a record by its unique code.
     * Returns null when not found.
     */
    public function findByCode(string $code): ?BaseMasterDataModel
    {
        /** @var BaseMasterDataModel|null */
        return $this->model
            ->where('code', $code)
            ->first();
    }

    /**
     * Paginate records with optional search and filters.
     */
    public function paginate(
        int     $perPage    = 15,
        ?string $search     = null,
        bool    $activeOnly = false,
        string  $sortBy     = 'name',
        string  $sortDir    = 'asc',
    ): LengthAwarePaginator {
        $query = $this->model->newQuery();

        if ($activeOnly) {
            $query->active();
        }

        if ($search !== null && $search !== '') {
            $query->search($search);
        }

        $query = $this->applySafeSort($query, $sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Search records by keyword across code and name.
     *
     * @return Collection<int, BaseMasterDataModel>
     */
    public function search(string $keyword, bool $activeOnly = true): Collection
    {
        /** @var Collection<int, BaseMasterDataModel> */
        return $this->model
            ->when($activeOnly, fn (Builder $q) => $q->active())
            ->search($keyword)
            ->ordered()
            ->get();
    }

    /**
     * Check whether a code already exists in this table.
     */
    public function existsByCode(string $code, ?string $excludeId = null): bool
    {
        $query = $this->model->where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    // -------------------------------------------------------------------------
    // Status Operations
    // -------------------------------------------------------------------------

    /**
     * Set a record's status to active.
     *
     * @throws NotFoundException
     */
    public function activate(string $id): bool
    {
        $record = $this->findByIdOrFail($id);

        return (bool) $record->update(['is_active' => true]);
    }

    /**
     * Set a record's status to inactive.
     *
     * @throws NotFoundException
     */
    public function deactivate(string $id): bool
    {
        $record = $this->findByIdOrFail($id);

        return (bool) $record->update(['is_active' => false]);
    }

    // -------------------------------------------------------------------------
    // CRUD Operations
    // -------------------------------------------------------------------------

    /** @param  array<string, mixed> $data */
    public function create(array $data): BaseMasterDataModel
    {
        return $this->model->create($data);
    }

    /** @param  array<string, mixed> $data */
    public function update(string $id, array $data): BaseMasterDataModel
    {
        $record = $this->findByIdOrFail($id);
        $record->update($data);

        return $record->refresh();
    }

    /** @throws NotFoundException */
    public function delete(string $id): bool
    {
        $record = $this->findByIdOrFail($id);

        return (bool) $record->delete();
    }

    public function toggleActive(string $id): BaseMasterDataModel
    {
        $record = $this->findByIdOrFail($id);
        $record->update(['is_active' => ! $record->is_active]);

        return $record->refresh();
    }

    public function countByParent(string $parentColumn, string $parentId): int
    {
        return $this->model->where($parentColumn, $parentId)->count();
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Find a record by primary key or throw NotFoundException.
     *
     * @throws NotFoundException
     */
    private function findByIdOrFail(string $id): BaseMasterDataModel
    {
        $record = $this->model->find($id);

        if ($record === null) {
            throw new NotFoundException(
                class_basename($this->model) . " with ID [{$id}] not found."
            );
        }

        return $record;
    }

    /**
     * Apply sort with whitelist protection.
     * Falls back to name asc if the column is not whitelisted.
     */
    private function applySafeSort(
        \Illuminate\Database\Eloquent\Builder $query,
        string $sortBy,
        string $sortDir,
    ): \Illuminate\Database\Eloquent\Builder {
        $column    = in_array($sortBy, $this->sortable, true) ? $sortBy : 'name';
        $direction = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($column, $direction);
    }
}
