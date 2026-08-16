<?php

declare(strict_types=1);

namespace App\Platform\Logging\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'system_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'context'    => 'array',
            'line'       => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
