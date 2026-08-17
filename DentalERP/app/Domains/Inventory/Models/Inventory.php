<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Models;

use App\Core\Base\BaseModel;

class Inventory extends BaseModel
{
    protected $table = 'inventory_items';

    protected $casts = [
        'quantity'     => 'decimal:2',
        'min_quantity' => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'is_active'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}