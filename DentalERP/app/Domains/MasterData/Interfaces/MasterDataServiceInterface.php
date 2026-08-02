<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Interfaces;

use App\Domains\MasterData\DTO\MasterDataFilterDTO;
use App\Domains\MasterData\Models\BaseMasterDataModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * MasterDataServiceInterface
 *
 * Generic business operation contract for all Master Data reference tables.
 * All 18 reference services implement this interface via BaseMasterDataService.
 *
 * Design goal: One interface — zero duplication across all reference tables.
 *
 * Layer rule: Business logic only — no direct DB queries.
 */
interface MasterDataServiceInterface
{
    // -------------------------------------------------------------------------
    // Read Operations
    // -------------------------------------------------------------------------

    /**
     * Get all records, optionally filtered by active status.
     *
     * @param  bool                             $activeOnly
     * @return Collection<int, BaseMasterDataModel>
     */
    public function getAll(bool $activeOnly = true): Collection;

    /**
     * Get active records for dropdown / select lists.
     * Returns minimal fields: id, code, name.
     * Suitable for option lists in forms.
     *
     * @return Collection<int, BaseMasterDataModel>
     */
    public function getForDropdown(): Collection;

    /**
     * Find a record by primary key or throw NotFoundException.
     *
     * @param  string                  $id
     * @return BaseMasterDataModel
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function findById(string $id): BaseMasterDataModel;

    /**
     * Find a record by its unique code.
     * Returns null when not found.
     *
     * @param  string                        $code
     * @return BaseMasterDataModel|null
     */
    public function findByCode(string $code): ?BaseMasterDataModel;

    /**
     * Search records by keyword across code and name.
     * Always returns only active records.
     *
     * @param  string                           $keyword
     * @return Collection<int, BaseMasterDataModel>
     */
    public function search(string $keyword): Collection;

    /**
     * Get a paginated list of records with optional search and filters.
     *
     * @param  MasterDataFilterDTO $filter
     * @return LengthAwarePaginator
     */
    public function paginate(MasterDataFilterDTO $filter): LengthAwarePaginator;

    // -------------------------------------------------------------------------
    // Write Operations
    // -------------------------------------------------------------------------

    /**
     * Activate a Master Data record inside a database transaction.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function activate(string $id): bool;

    /**
     * Deactivate a Master Data record inside a database transaction.
     *
     * @param  string $id
     * @return bool
     *
     * @throws \App\Core\Exceptions\NotFoundException
     */
    public function deactivate(string $id): bool;
}
