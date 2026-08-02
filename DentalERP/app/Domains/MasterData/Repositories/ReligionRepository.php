<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Religion;

/**
 * ReligionRepository
 *
 * Data access for the religions reference table.
 */
class ReligionRepository extends BaseMasterDataRepository
{
    public function __construct(Religion $model)
    {
        parent::__construct($model);
    }
}
