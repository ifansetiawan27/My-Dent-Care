<?php

declare(strict_types=1);

namespace App\Domains\Billing\Models;

use App\Core\Base\BaseModel;

class Billing extends BaseModel
{
    protected $table = 'invoices';

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'items'        => 'array',
        'due_date'     => 'date',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}