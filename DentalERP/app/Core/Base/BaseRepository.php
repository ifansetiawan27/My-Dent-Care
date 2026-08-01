<?php

declare(strict_types=1);

namespace App\Core\Base;

use App\Core\Contracts\RepositoryInterface;
use App\Core\Exceptions\NotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance.
     */
    protected Model $model;

    /**
     * Columns that are searchable via the search() method.
     * Override in child repositories to enable full-text search.
     *
     * @var array<string>
     */
    protected array $searchable = [];

    /**
     * Columns that are allowed for filtering.
     * Override in child repositories to whitelist filterable columns.
     *
     * @var array<string>
     */
    protected array $filterable = [];

    /**
     * Columns that are allowed for sorting.
     * Override in child repositories to whitelist sortable columns.
     *
     * @var array<string>
     */
    protected array $sortable = [
        'created_at',
        'updated_at',
    ];

    /**
     * Inject the model via constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Get all records.
     *
     * @param  array<string>        $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->select($columns)->get();
    }

    /**
     * Find a record by primary key.
     */
    public function find(string $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by primary key or throw NotFoundException.
     *
     * @throws NotFoundException
     */
    public function findOrFail(string $id): Model
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
     * Find records by a specific field value.
     *
     * @param  array<string>        $columns
     * @return Collection<int, Model>
     */
    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model
            ->select($columns)
            ->where($field, $value)
            ->get();
    }

    /**
     * Find the first record matching a field value.
     *
     * @param  array<string> $columns
     */
    public function findOneByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model
            ->select($columns)
            ->where($field, $value)
            ->first();
    }

    /**
     * Create a new record.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record by primary key.
     *
     * @param  array<string, mixed> $data
     * @throws NotFoundException
     */
    public function update(string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    /**
     * Soft delete a record by primary key.
     *
     * @throws NotFoundException
     */
    public function delete(string $id): bool
    {
        $record = $this->findOrFail($id);

        return (bool) $record->delete();
    }

    /**
     * Permanently delete a record by primary key (including soft-deleted).
     *
     * @throws NotFoundException
     */
    public function forceDelete(string $id): bool
    {
        $record = $this->model->withTrashed()->find($id);

        if ($record === null) {
            throw new NotFoundException(
                class_basename($this->model) . " with ID [{$id}] not found."
            );
        }

        return (bool) $record->forceDelete();
    }

    /**
     * Restore a soft-deleted record by primary key.
     *
     * @throws NotFoundException
     */
    public function restore(string $id): bool
    {
        $record = $this->model->withTrashed()->find($id);

        if ($record === null) {
            throw new NotFoundException(
                class_basename($this->model) . " with ID [{$id}] not found."
            );
        }

        return (bool) $record->restore();
    }

    // -------------------------------------------------------------------------
    // PAGINATION + SEARCH + FILTER + SORT
    // -------------------------------------------------------------------------

    /**
     * Paginate records with optional search, filter, and sort.
     *
     * @param  array<string, mixed> $filters
     * @param  array<string>        $columns
     */
    public function paginate(
        int     $perPage = 15,
        array   $filters = [],
        ?string $search  = null,
        string  $sortBy  = 'created_at',
        string  $sortDir = 'desc',
        array   $columns = ['*'],
    ): LengthAwarePaginator {
        $query = $this->model->select($columns);

        // Apply search
        if ($search !== null && $search !== '' && ! empty($this->searchable)) {
            $query->where(function ($q) use ($search): void {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'ILIKE', "%{$search}%");
                }
            });
        }

        // Apply filters
        if (! empty($filters)) {
            $query = $this->applyFilters($query, $filters);
        }

        // Apply sort
        $query = $this->applySort($query, $sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    // -------------------------------------------------------------------------
    // TRANSACTION
    // -------------------------------------------------------------------------

    /**
     * Execute a callable inside a database transaction.
     *
     * @template T
     * @param  callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    // -------------------------------------------------------------------------
    // INTERNAL HELPERS
    // -------------------------------------------------------------------------

    /**
     * Apply column filters to the query.
     * Only whitelisted columns in $this->filterable are applied.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array<string, mixed>                  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters(
        \Illuminate\Database\Eloquent\Builder $query,
        array $filters,
    ): \Illuminate\Database\Eloquent\Builder {
        foreach ($filters as $column => $value) {
            if (! in_array($column, $this->filterable, true)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query;
    }

    /**
     * Apply sorting to the query.
     * Only whitelisted columns in $this->sortable are applied.
     * Falls back to created_at desc when column is not whitelisted.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySort(
        \Illuminate\Database\Eloquent\Builder $query,
        string $sortBy,
        string $sortDir,
    ): \Illuminate\Database\Eloquent\Builder {
        $direction = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, $this->sortable, true)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $direction);
    }
}
