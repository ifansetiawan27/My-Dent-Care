<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\DoctorSpecialtyRepository;

/**
 * DoctorSpecialtyService
 *
 * Business operations for the doctor_specialties reference table.
 */
class DoctorSpecialtyService extends BaseMasterDataService
{
    public function __construct(DoctorSpecialtyRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'DoctorSpecialtyService';
    }
}
