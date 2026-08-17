<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Models;

use App\Core\Base\BaseModel;

class Treatment extends BaseModel
{
    protected $table = 'treatments';

    protected $casts = [
        'cost'           => 'decimal:2',
        'procedure_data' => 'array',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}