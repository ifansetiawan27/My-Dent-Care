<?php

declare(strict_types=1);

namespace App\Platform\Notification\Models;

use App\Core\Base\BaseModel;

class Notification extends BaseModel
{
    protected $table = 'notifications';

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'data'    => 'array',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }
}
