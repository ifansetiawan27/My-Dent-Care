<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Models;

use App\Core\Base\BaseModel;

class Dashboard extends BaseModel
{
    protected $table = 'dashboards';

    protected $casts = [
        'config'     => 'array',
        'widgets'    => 'array',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}