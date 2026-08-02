<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Currency;

/**
 * CurrencyRepository
 *
 * Data access for the currencies reference table.
 */
class CurrencyRepository extends BaseMasterDataRepository
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }
}
