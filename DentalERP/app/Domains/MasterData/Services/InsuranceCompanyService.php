<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\InsuranceCompanyRepository;

/**
 * InsuranceCompanyService
 *
 * Business operations for the insurance_companies reference table.
 */
class InsuranceCompanyService extends BaseMasterDataService
{
    public function __construct(InsuranceCompanyRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'InsuranceCompanyService';
    }
}
