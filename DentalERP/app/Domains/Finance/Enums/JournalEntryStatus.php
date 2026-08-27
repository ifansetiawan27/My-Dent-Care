<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum JournalEntryStatus: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::POSTED => 'Posted',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canBePosted(): bool
    {
        return $this === self::DRAFT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::DRAFT, self::POSTED]);
    }
}
