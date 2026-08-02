<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * AppointmentStatus Enum — used by Appointment domain.
 */
enum AppointmentStatus: string
{
    case Scheduled  = 'scheduled';
    case Confirmed  = 'confirmed';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case NoShow     = 'no_show';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Scheduled  => 'Scheduled',
            self::Confirmed  => 'Confirmed',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
            self::NoShow     => 'No Show',
        };
    }

    /**
     * Determine whether this status is a terminal state.
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled, self::NoShow => true,
            default => false,
        };
    }

    /**
     * Determine whether the appointment is still active.
     */
    public function isActive(): bool
    {
        return match ($this) {
            self::Scheduled, self::Confirmed, self::InProgress => true,
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
