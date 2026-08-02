<?php

declare(strict_types=1);

namespace App\Platform\Notification\Enums;

/**
 * NotificationStatus
 *
 * Lifecycle status of a dispatched notification.
 */
enum NotificationStatus: string
{
    case Pending = 'pending';
    case Sent    = 'sent';
    case Failed  = 'failed';
    case Read    = 'read';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Sent    => 'Sent',
            self::Failed  => 'Failed',
            self::Read    => 'Read',
        };
    }

    /**
     * Whether this status is terminal (no further transitions expected).
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Read, self::Failed => true,
            default => false,
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
