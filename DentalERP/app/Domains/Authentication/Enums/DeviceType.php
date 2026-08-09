<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Enums;

enum DeviceType: string
{
    case Web    = 'web';
    case Mobile = 'mobile';
    case Tablet = 'tablet';
    case Api    = 'api';

    public function label(): string
    {
        return match ($this) {
            self::Web    => 'Web',
            self::Mobile => 'Mobile',
            self::Tablet => 'Tablet',
            self::Api    => 'API',
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
