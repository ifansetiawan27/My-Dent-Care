<?php

declare(strict_types=1);

namespace App\Domains\AI\Models;

use App\Core\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AI extends BaseModel
{
    protected $table = 'ai_queries';

    protected $casts = [
        'prompt'      => 'encrypted',
        'response'    => 'encrypted',
        'tokens_used' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Organization\Models\Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\User\Models\User::class);
    }
}