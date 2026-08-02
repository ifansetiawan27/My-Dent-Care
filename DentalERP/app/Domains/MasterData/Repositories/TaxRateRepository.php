<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\TaxRate;

/**
 * TaxRateRepository
 *
 * Data access for the tax_rates reference table.
 */
class TaxRateRepository extends BaseMasterDataRepository
{
    public function __construct(TaxRate $model)
    {
        parent::__construct($model);
    }
}
