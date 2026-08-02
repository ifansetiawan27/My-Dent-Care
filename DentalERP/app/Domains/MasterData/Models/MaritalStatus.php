<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * MaritalStatus
 *
 * Marital status reference (Single, Married, Divorced, Widowed).
 * Uses the base structure only (code, name, is_active).
 */
class MaritalStatus extends BaseMasterDataModel
{
    protected $table = 'marital_statuses';
}
