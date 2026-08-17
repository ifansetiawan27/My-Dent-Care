<?php

declare(strict_types=1);

namespace App\Domains\CRM\Models;

use App\Core\Base\BaseModel;

class CRM extends BaseModel
{
    protected $table = 'crm_contacts';

    protected $casts = [
        'follow_up_date' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];
}