<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Interfaces\HierarchicalRepositoryInterface;
use App\Domains\MasterData\Models\Province;
use App\Domains\MasterData\Traits\HasParentColumn;

/**
 * ProvinceRepository
 *
 * Data access for the provinces reference table.
 * Supports parent-scoped queries by country via HasParentColumn.
 */
class ProvinceRepository extends BaseMasterDataRepository implements HierarchicalRepositoryInterface
{
    use HasParentColumn;

    /**
     * Parent foreign key for hierarchical scoping.
     */
    protected string $parentColumn = 'country_id';

    public function __construct(Province $model)
    {
        parent::__construct($model);
    }
}
