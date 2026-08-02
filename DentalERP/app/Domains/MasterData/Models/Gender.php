<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * Gender
 *
 * Gender reference (Male, Female).
 * Uses the base structure only (code, name, is_active).
 *
 * Note: For type-safe comparisons in business logic, use the
 * App\Core\Enums\Gender enum. This table provides UI-selectable records.
 */
class Gender extends BaseMasterDataModel
{
    protected $table = 'genders';
}
