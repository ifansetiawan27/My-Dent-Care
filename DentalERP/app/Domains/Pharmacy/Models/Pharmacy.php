<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Models;

use App\Core\Base\BaseModel;

class Pharmacy extends BaseModel
{
    protected $table = 'pharmacy_items';

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'expiry_date' => 'date',
        'is_active'   => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];
}