<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * VisitStatus Enum — used by EMR domain for patient visit tracking.
 */
enum VisitStatus: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open       => 'Open',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    /**
     * Determine whether this status is a terminal state.
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled => true,
            default => false,
        };
    }

    /**
     * Return all case values as a plain array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
