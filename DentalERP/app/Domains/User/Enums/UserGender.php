<?php

declare(strict_types=1);

namespace App\Domains\User\Enums;

enum UserGender: string
{
    case Male   = 'male';
    case Female = 'female';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Male   => 'Male',
            self::Female => 'Female',
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
