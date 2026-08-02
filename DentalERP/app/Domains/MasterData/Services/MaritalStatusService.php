<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\MaritalStatusRepository;

/**
 * MaritalStatusService
 *
 * Business operations for the marital_statuses reference table.
 */
class MaritalStatusService extends BaseMasterDataService
{
    public function __construct(MaritalStatusRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'MaritalStatusService';
    }
}
