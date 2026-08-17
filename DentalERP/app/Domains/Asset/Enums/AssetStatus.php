<?php

declare(strict_types=1);

namespace App\Domains\Asset\Enums;

enum AssetStatus: string
{
    case Active      = 'active';
    case Maintenance = 'maintenance';
    case Retired     = 'retired';
    case Disposed    = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::Active      => 'Active',
            self::Maintenance => 'Maintenance',
            self::Retired     => 'Retired',
            self::Disposed    => 'Disposed',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Disposed => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}