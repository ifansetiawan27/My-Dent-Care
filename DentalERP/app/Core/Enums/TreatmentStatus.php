<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * TreatmentStatus — Canonical Core Enum
 *
 * Lifecycle status of a dental treatment plan or session.
 * Used by Treatment domain and referenced by Billing, EMR.
 */
enum TreatmentStatus: string
{
    case Planned    = 'planned';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned    => 'Planned',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled => true,
            default => false,
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::Planned, self::InProgress => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
