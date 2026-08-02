<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\PatientTypeRepository;

/**
 * PatientTypeService
 *
 * Business operations for the patient_types reference table.
 */
class PatientTypeService extends BaseMasterDataService
{
    public function __construct(PatientTypeRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'PatientTypeService';
    }
}
