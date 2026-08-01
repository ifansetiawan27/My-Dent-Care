<?php

declare(strict_types=1);

namespace App\Core\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface extends CrudInterface
{
    /**
     * Get all records.
     *
     * @param  array<string>    $columns
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find records by a specific field value.
     *
     * @param  string           $field
     * @param  mixed            $value
     * @param  array<string>    $columns
     * @return Collection<int, Model>
     */
    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection;

    /**
     * Find the first record matching a field value.
     *
     * @param  string      $field
     * @param  mixed       $value
     * @param  array<string> $columns
     * @return Model|null
     */
    public function findOneByField(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Paginate records with optional search, filter, and sort.
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
        int    $perPage  = 15,
        array  $filters  = [],
        ?string $search  = null,
        string $sortBy   = 'created_at',
        string $sortDir  = 'desc',
        array  $columns  = ['*'],
    ): LengthAwarePaginator;

    /**
     * Execute a callable inside a database transaction.
     *
     * @template T
     * @param  callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed;
}
