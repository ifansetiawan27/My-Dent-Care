<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Traits;

use Illuminate\Support\Collection;

/**
 * HasParentColumn
 *
 * Provides parent-scoped querying for hierarchical Master Data repositories.
 * The consuming repository MUST define `protected string $parentColumn`
 * (e.g. 'country_id') and extend BaseMasterDataRepository (which exposes $model).
 *
 * @property \App\Domains\MasterData\Models\BaseMasterDataModel $model
 */
trait HasParentColumn
{
    /**
     * The foreign key column that references the parent record.
     * Override in the consuming repository.
     */
    protected string $parentColumn = '';

    /**
     * Get active child records that belong to the given parent.
     *
     * @param  array<string>      $columns
     * @return Collection<int, \App\Domains\MasterData\Models\BaseMasterDataModel>
     */
    public function findByParent(string $parentId, array $columns = ['id', 'code', 'name']): Collection
    {
        /** @var Collection<int, \App\Domains\MasterData\Models\BaseMasterDataModel> */
        return $this->model
            ->select($columns)
            ->where($this->parentColumn, $parentId)
            ->active()
            ->ordered()
            ->get();
    }
}
