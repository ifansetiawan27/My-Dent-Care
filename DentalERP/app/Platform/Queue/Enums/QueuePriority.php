<?php

declare(strict_types=1);

namespace App\Platform\Queue\Enums;

/**
 * QueuePriority
 *
 * Priority lanes for background jobs dispatched via the Queue Platform.
 * Maps to distinct queue connections/names for worker prioritization.
 */
enum QueuePriority: string
{
    case High    = 'high';
    case Default = 'default';
    case Low     = 'low';

    /**
     * The queue name associated with this priority.
     */
    public function queueName(): string
    {
        return $this->value;
    }

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::High    => 'High Priority',
            self::Default => 'Default',
            self::Low     => 'Low Priority',
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
