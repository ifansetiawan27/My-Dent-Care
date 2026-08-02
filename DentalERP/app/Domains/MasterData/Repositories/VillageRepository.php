<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Interfaces\HierarchicalRepositoryInterface;
use App\Domains\MasterData\Models\Village;
use App\Domains\MasterData\Traits\HasParentColumn;

/**
 * VillageRepository
 *
 * Data access for the villages reference table.
 * Supports parent-scoped queries by district via HasParentColumn.
 */
class VillageRepository extends BaseMasterDataRepository implements HierarchicalRepositoryInterface
{
    use HasParentColumn;

    protected string $parentColumn = 'district_id';

    public function __construct(Village $model)
    {
        parent::__construct($model);
    }
}
