<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Enums;

enum LoginStatus: string
{
    case Success = 'success';
    case Failed  = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::Failed  => 'Failed',
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
