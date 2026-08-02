<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Interfaces\HierarchicalRepositoryInterface;
use App\Domains\MasterData\Models\District;
use App\Domains\MasterData\Traits\HasParentColumn;

/**
 * DistrictRepository
 *
 * Data access for the districts reference table.
 * Supports parent-scoped queries by city via HasParentColumn.
 */
class DistrictRepository extends BaseMasterDataRepository implements HierarchicalRepositoryInterface
{
    use HasParentColumn;

    protected string $parentColumn = 'city_id';

    public function __construct(District $model)
    {
        parent::__construct($model);
    }
}
