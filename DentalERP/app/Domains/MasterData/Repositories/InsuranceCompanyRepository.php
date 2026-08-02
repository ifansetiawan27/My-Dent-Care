<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\InsuranceCompany;

/**
 * InsuranceCompanyRepository
 *
 * Data access for the insurance_companies reference table.
 */
class InsuranceCompanyRepository extends BaseMasterDataRepository
{
    public function __construct(InsuranceCompany $model)
    {
        parent::__construct($model);
    }
}
