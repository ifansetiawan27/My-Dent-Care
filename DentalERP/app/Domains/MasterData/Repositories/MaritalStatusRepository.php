<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\MaritalStatus;

/**
 * MaritalStatusRepository
 *
 * Data access for the marital_statuses reference table.
 */
class MaritalStatusRepository extends BaseMasterDataRepository
{
    public function __construct(MaritalStatus $model)
    {
        parent::__construct($model);
    }
}
