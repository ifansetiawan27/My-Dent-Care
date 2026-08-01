<?php

declare(strict_types=1);

namespace App\Core\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface ServiceInterface
{
    /**
     * Paginate records with optional search, filter, and sort.
     *
     * @param  array<string, mixed> $params
     */
    public function paginate(array $params = []): LengthAwarePaginator;

    /**
     * Find a record by primary key or throw NotFoundException.
     */
    public function getById(string $id): Model;

    /**
     * Create a new record inside a transaction.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): Model;

    /**
     * Update an existing record inside a transaction.
     *
     * @param  array<string, mixed> $data
     */
    public function update(string $id, array $data): Model;

    /**
     * Soft delete a record inside a transaction.
     */
    public function delete(string $id): bool;
}
