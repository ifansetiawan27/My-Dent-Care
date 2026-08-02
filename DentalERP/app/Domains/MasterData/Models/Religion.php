<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * Religion
 *
 * Religion reference (Islam, Christian, Catholic, Hindu, Buddha, Konghucu).
 * Uses the base structure only (code, name, is_active).
 */
class Religion extends BaseMasterDataModel
{
    protected $table = 'religions';
}
