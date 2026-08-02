<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\TimezoneRepository;

/**
 * TimezoneService
 *
 * Business operations for the timezones reference table.
 */
class TimezoneService extends BaseMasterDataService
{
    public function __construct(TimezoneRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'TimezoneService';
    }
}
