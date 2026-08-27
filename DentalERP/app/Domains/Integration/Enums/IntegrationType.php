<?php

declare(strict_types=1);

namespace App\Domains\Integration\Enums;

enum IntegrationType: string
{
    case Satusehat = 'satusehat';
    case Bpjs = 'bpjs';
    case Midtrans = 'midtrans';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Satusehat => 'SATUSEHAT',
            self::Bpjs => 'BPJS',
            self::Midtrans => 'Midtrans',
            self::Custom => 'Custom',
        };
    }

    public function isHealthcare(): bool
    {
        return match ($this) {
            self::Satusehat, self::Bpjs => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
