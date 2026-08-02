<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\GenderRepository;

/**
 * GenderService
 *
 * Business operations for the genders reference table.
 */
class GenderService extends BaseMasterDataService
{
    public function __construct(GenderRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'GenderService';
    }
}
