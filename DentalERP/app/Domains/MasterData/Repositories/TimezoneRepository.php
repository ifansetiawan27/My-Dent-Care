<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Timezone;

/**
 * TimezoneRepository
 *
 * Data access for the timezones reference table.
 */
class TimezoneRepository extends BaseMasterDataRepository
{
    public function __construct(Timezone $model)
    {
        parent::__construct($model);
    }
}
