<?php

declare(strict_types=1);

namespace App\Platform\Audit\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $table = 'audit_logs';

    protected function casts(): array
    {
        return [
            'old_value'  => 'array',
            'new_value'  => 'array',
            'created_at' => 'datetime',
        ];
    }
}
