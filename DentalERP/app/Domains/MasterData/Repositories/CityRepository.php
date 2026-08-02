<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Interfaces\HierarchicalRepositoryInterface;
use App\Domains\MasterData\Models\City;
use App\Domains\MasterData\Traits\HasParentColumn;

/**
 * CityRepository
 *
 * Data access for the cities reference table.
 * Supports parent-scoped queries by province via HasParentColumn.
 */
class CityRepository extends BaseMasterDataRepository implements HierarchicalRepositoryInterface
{
    use HasParentColumn;

    protected string $parentColumn = 'province_id';

    public function __construct(City $model)
    {
        parent::__construct($model);
    }
}
