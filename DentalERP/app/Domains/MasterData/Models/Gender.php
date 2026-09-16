<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * Gender
 *
 * Gender reference (Male, Female).
 * Uses the base structure only (code, name, is_active).
 */
class Gender extends BaseMasterDataModel
{
    protected $table = 'genders';
}
