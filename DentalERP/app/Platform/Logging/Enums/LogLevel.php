<?php

declare(strict_types=1);

namespace App\Platform\Logging\Enums;

/**
 * LogLevel
 *
 * PSR-3 / RFC 5424 log severity levels, ordered from most to least severe.
 * Used by the Logging Platform to classify and route log messages.
 */
enum LogLevel: string
{
    case Emergency = 'emergency';
    case Alert     = 'alert';
    case Critical  = 'critical';
    case Error     = 'error';
    case Warning   = 'warning';
    case Notice    = 'notice';
    case Info      = 'info';
    case Debug     = 'debug';

    /**
     * Numeric severity — lower number means more severe (RFC 5424).
     */
    public function severity(): int
    {
        return match ($this) {
            self::Emergency => 0,
            self::Alert     => 1,
            self::Critical  => 2,
            self::Error     => 3,
            self::Warning   => 4,
            self::Notice    => 5,
            self::Info      => 6,
            self::Debug     => 7,
        };
    }

    /**
     * Whether this level must be persisted to the database.
     * Warning and above are persisted; lower levels go to file only.
     */
    public function shouldPersist(): bool
    {
        return $this->severity() <= self::Warning->severity();
    }

    /**
     * Whether this level should be forwarded to external monitoring.
     * Error and above are forwarded.
     */
    public function shouldForwardExternal(): bool
    {
        return $this->severity() <= self::Error->severity();
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
