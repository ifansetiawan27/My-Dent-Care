<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\ReligionRepository;

/**
 * ReligionService
 *
 * Business operations for the religions reference table.
 */
class ReligionService extends BaseMasterDataService
{
    public function __construct(ReligionRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'ReligionService';
    }
}
