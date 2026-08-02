<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Interfaces;

use Illuminate\Support\Collection;

/**
 * HierarchicalRepositoryInterface
 *
 * Contract for Master Data tables that belong to a parent record
 * (e.g. Province → Country, City → Province). Enables cascading
 * dropdown queries scoped to a parent identifier.
 *
 * Implemented by geographic repositories via the HasParentColumn trait.
 */
interface HierarchicalRepositoryInterface
{
    /**
     * Get active child records that belong to the given parent.
     * Ordered by name — suitable for cascading select lists.
     *
     * @param  string             $parentId  UUID of the parent record.
     * @param  array<string>      $columns
     * @return Collection<int, \App\Domains\MasterData\Models\BaseMasterDataModel>
     */
    public function findByParent(string $parentId, array $columns = ['id', 'code', 'name']): Collection;
}
