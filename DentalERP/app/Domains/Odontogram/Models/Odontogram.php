<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Models;

use App\Core\Base\BaseModel;

class Odontogram extends BaseModel
{
    protected $table = 'odontograms';

    protected $casts = [
        'findings'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}