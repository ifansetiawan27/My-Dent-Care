<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\BloodType;

/**
 * BloodTypeRepository
 *
 * Data access for the blood_types reference table.
 */
class BloodTypeRepository extends BaseMasterDataRepository
{
    public function __construct(BloodType $model)
    {
        parent::__construct($model);
    }
}
