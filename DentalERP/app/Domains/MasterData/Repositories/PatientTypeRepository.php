<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\PatientType;

/**
 * PatientTypeRepository
 *
 * Data access for the patient_types reference table.
 */
class PatientTypeRepository extends BaseMasterDataRepository
{
    public function __construct(PatientType $model)
    {
        parent::__construct($model);
    }
}
