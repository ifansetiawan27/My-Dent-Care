<?php

declare(strict_types=1);

namespace App\Domains\HR\Models;

use App\Core\Base\BaseModel;

class HR extends BaseModel
{
    protected $table = 'hr_records';

    protected $casts = [
        'effective_date' => 'date',
        'end_date'       => 'date',
        'data'           => 'array',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}