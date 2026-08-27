<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReport extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'financial_reports';

    protected $fillable = [
        'organization_id',
        'report_type',
        'report_name',
        'period_start',
        'period_end',
        'filters',
        'report_data',
        'status',
        'export_format',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'filters' => 'array',
        'report_data' => 'array',
        'generated_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Organization\Models\Organization::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeGenerated($query)
    {
        return $query->where('status', 'generated');
    }
}
