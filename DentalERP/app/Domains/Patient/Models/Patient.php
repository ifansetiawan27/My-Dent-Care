<?php

declare(strict_types=1);

namespace App\Domains\Patient\Models;

use App\Core\Base\BaseModel;

class Patient extends BaseModel
{
    protected $table = 'patients';

    protected $casts = [
        'birth_date' => 'date',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}