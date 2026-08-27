<?php

declare(strict_types=1);

namespace App\Domains\Integration\Enums;

enum IntegrationStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Pending = 'pending';
    case Retrying = 'retrying';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Success',
            self::Failed => 'Failed',
            self::Pending => 'Pending',
            self::Retrying => 'Retrying',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Success, self::Failed => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
