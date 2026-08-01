<?php

declare(strict_types=1);

namespace App\Core\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CrudInterface
{
    /**
     * Find a record by primary key.
     *
     * @param  string       $id
     * @return Model|null
     */
    public function find(string $id): ?Model;

    /**
     * Find a record by primary key or throw an exception.
     *
     * @param  string $id
     * @return Model
     */
    public function findOrFail(string $id): Model;

    /**
     * Create a new record.
     *
     * @param  array<string, mixed> $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing record by primary key.
     *
     * @param  string               $id
     * @param  array<string, mixed> $data
     * @return Model
     */
    public function update(string $id, array $data): Model;

    /**
     * Soft delete a record by primary key.
     *
     * @param  string $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Permanently delete a record by primary key.
     *
     * @param  string $id
     * @return bool
     */
    public function forceDelete(string $id): bool;

    /**
     * Restore a soft-deleted record by primary key.
     *
     * @param  string $id
     * @return bool
     */
    public function restore(string $id): bool;
}
