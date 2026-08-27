<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Core\Traits\HasCreatedBy;
use App\Core\Traits\HasUpdatedBy;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory, HasUuid, HasCreatedBy, HasUpdatedBy;

    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'entry_type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
