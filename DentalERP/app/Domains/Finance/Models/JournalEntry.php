<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $table = 'journal_entries';

    protected $fillable = [
        'organization_id',
        'entry_number',
        'reference_type',
        'reference_id',
        'entry_date',
        'period_date',
        'description',
        'status',
        'total_debit',
        'total_credit',
        'is_balanced',
        'posted_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'period_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'is_balanced' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Organization\Models\Organization::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $this->byStatus($query, 'draft');
    }

    public function scopePosted($query)
    {
        return $this->byStatus($query, 'posted');
    }

    public function scopeForPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }
}
