<?php

declare(strict_types=1);

namespace App\Domains\Doctor\Models;

use App\Core\Base\BaseModel;

class Doctor extends BaseModel
{
    protected $table = 'doctors';

    protected $casts = [
        'hire_date'        => 'date',
        'resignation_date' => 'date',
        'consultation_fee' => 'decimal:2',
        'is_active'        => 'boolean',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];
}