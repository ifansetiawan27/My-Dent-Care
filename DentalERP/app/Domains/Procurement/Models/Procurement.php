<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Models;

use App\Core\Base\BaseModel;

class Procurement extends BaseModel
{
    protected $table = 'procurement_orders';

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'total_amount'  => 'decimal:2',
        'items'         => 'array',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];
}