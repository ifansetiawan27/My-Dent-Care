<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\Country;

/**
 * CountryRepository
 *
 * Data access for the countries reference table.
 * Inherits all query behavior from BaseMasterDataRepository.
 */
class CountryRepository extends BaseMasterDataRepository
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }
}
