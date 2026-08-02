<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\CurrencyRepository;

/**
 * CurrencyService
 *
 * Business operations for the currencies reference table.
 */
class CurrencyService extends BaseMasterDataService
{
    public function __construct(CurrencyRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'CurrencyService';
    }
}
