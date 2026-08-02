<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Interfaces;

use App\Domains\MasterData\Models\BaseMasterDataModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * MasterDataRepositoryInterface
 *
 * Generic data-access contract for all Master Data reference tables.
 * All 18 reference repositories extend this interface.
 *
 * Design goal: One interface — zero duplication across all reference tables.
 * Each concrete repository simply extends BaseMasterDataRepository.
 *
 * Layer rule: Only database queries — no business logic.
 */
interface MasterDataRepositoryInterface
{
    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get all records, optionally filtered by active status.
     * Use $activeOnly = true for dropdowns and public-facing lists.
     *
     * @param  bool                             $activeOnly  Filter to active records only.
     * @param  array<string>                    $columns     Columns to select.
     * @return Collection<int, BaseMasterDataModel>
     */
    public function findAll(bool $activeOnly = true, array $columns = ['*']): Collection;

    /**
     * Get only active records ordered by name.
     * Optimized shorthand for dropdown / select list use cases.
     *
     * @param  array<string>                    $columns
     * @return Collection<int, BaseMasterDataModel>
     */
    public function findActive(array $columns = ['id', 'code', 'name']): Collection;

    /**
     * Find a record by primary key.
     * Returns null when not found.
     *
     * @param  string                        $id
     * @return BaseMasterDataModel|null
     */
    public function findById(string $id): ?BaseMasterDataModel;

    /**
     * Find a record by its unique code.
     * Returns null when not found.
     *
     * @param  string                        $code
     * @return BaseMasterDataModel|null
     */
    public function findByCode(string $code): ?BaseMasterDataModel;

    /**
     * Paginate records with optional search and active filter.
     *
     * @param  int         $perPage
     * @param  string|null $search      Keyword for code/name ILIKE search.
     * @param  bool        $activeOnly  Include only active records when true.
     * @param  string      $sortBy      Column to sort by.
     * @param  string      $sortDir     Sort direction: asc|desc.
     * @return LengthAwarePaginator
     */
    public function paginate(
        int     $perPage    = 15,
        ?string $search     = null,
        bool    $activeOnly = false,
        string  $sortBy     = 'name',
        string  $sortDir    = 'asc',
    ): LengthAwarePaginator;

    /**
     * Search records by keyword across code and name columns.
     *
     * @param  string                           $keyword
     * @param  bool                             $activeOnly
     * @return Collection<int, BaseMasterDataModel>
     */
    public function search(string $keyword, bool $activeOnly = true): Collection;

    /**
     * Check whether a code already exists in this table.
     * Optionally excludes a specific record ID (for update self-comparison).
     *
     * @param  string      $code
     * @param  string|null $excludeId
     * @return bool
     */
    public function existsByCode(string $code, ?string $excludeId = null): bool;

    // -------------------------------------------------------------------------
    // Status Operations
    // -------------------------------------------------------------------------

    /**
     * Set a record's status to active.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function activate(string $id): bool;

    /**
     * Set a record's status to inactive.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function deactivate(string $id): bool;
}
