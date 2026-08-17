<?php

declare(strict_types=1);

namespace App\Domains\Asset\Models;

use App\Core\Base\BaseModel;

class Asset extends BaseModel
{
    protected $table = 'assets';

    protected $casts = [
        'purchase_date'   => 'date',
        'purchase_price'  => 'decimal:2',
        'warranty_expiry' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];
}