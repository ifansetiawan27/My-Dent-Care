<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

use App\Domains\MasterData\Models\BaseMasterDataModel;

class AppointmentStatus extends BaseMasterDataModel
{
    protected $table = 'appointment_statuses';
}
