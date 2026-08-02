<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * BloodType
 *
 * Blood type reference (A, B, AB, O with Rh variants).
 * Uses the base structure only (code, name, is_active).
 */
class BloodType extends BaseMasterDataModel
{
    protected $table = 'blood_types';
}
