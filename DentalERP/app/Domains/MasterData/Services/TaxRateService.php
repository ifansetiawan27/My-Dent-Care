<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\TaxRateRepository;

/**
 * TaxRateService
 *
 * Business operations for the tax_rates reference table.
 */
class TaxRateService extends BaseMasterDataService
{
    public function __construct(TaxRateRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'TaxRateService';
    }
}
