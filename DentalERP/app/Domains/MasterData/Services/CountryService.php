<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\CountryRepository;

/**
 * CountryService
 *
 * Business operations for the countries reference table.
 * Inherits all behavior from BaseMasterDataService.
 */
class CountryService extends BaseMasterDataService
{
    public function __construct(CountryRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'CountryService';
    }
}
