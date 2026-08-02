<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\BloodTypeRepository;

/**
 * BloodTypeService
 *
 * Business operations for the blood_types reference table.
 */
class BloodTypeService extends BaseMasterDataService
{
    public function __construct(BloodTypeRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'BloodTypeService';
    }
}
