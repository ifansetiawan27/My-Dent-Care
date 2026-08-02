<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\DoctorSpecialty;

/**
 * DoctorSpecialtyRepository
 *
 * Data access for the doctor_specialties reference table.
 */
class DoctorSpecialtyRepository extends BaseMasterDataRepository
{
    public function __construct(DoctorSpecialty $model)
    {
        parent::__construct($model);
    }
}
