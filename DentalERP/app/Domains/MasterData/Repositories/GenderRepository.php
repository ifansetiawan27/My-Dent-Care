<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Gender;

/**
 * GenderRepository
 *
 * Data access for the genders reference table.
 */
class GenderRepository extends BaseMasterDataRepository
{
    public function __construct(Gender $model)
    {
        parent::__construct($model);
    }
}
