<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Models;

use App\Core\Base\BaseModel;

class File extends BaseModel
{
    protected $table = 'files';

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'size' => 'integer',
        ];
    }
}
