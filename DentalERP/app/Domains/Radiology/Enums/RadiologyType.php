<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Enums;

enum RadiologyType: string
{
    case Panoramic     = 'panoramic';
    case Periapical    = 'periapical';
    case Cephalometric = 'cephalometric';
    case CBCT          = 'cbct';
    case Occlusal      = 'occlusal';

    public function label(): string
    {
        return match ($this) {
            self::Panoramic     => 'Panoramic',
            self::Periapical    => 'Periapical',
            self::Cephalometric => 'Cephalometric',
            self::CBCT          => 'CBCT',
            self::Occlusal      => 'Occlusal',
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
