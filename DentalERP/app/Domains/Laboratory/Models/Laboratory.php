<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Models;

use App\Core\Base\BaseModel;

class Laboratory extends BaseModel
{
    protected $table = 'lab_orders';

    protected $casts = [
        'ordered_at'   => 'date',
        'completed_at' => 'date',
        'results'      => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}