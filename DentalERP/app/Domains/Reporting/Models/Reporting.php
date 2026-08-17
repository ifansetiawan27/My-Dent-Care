<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Models;

use App\Core\Base\BaseModel;

class Reporting extends BaseModel
{
    protected $table = 'reports';

    protected $casts = [
        'parameters'  => 'array',
        'data'        => 'array',
        'report_date' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}