<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Enums;

/**
 * StorageDriver
 *
 * Storage backends supported by the File Storage Platform.
 * The active driver is resolved from configuration — domains never choose it.
 */
enum StorageDriver: string
{
    case Local = 'local';
    case S3    = 's3';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Local => 'Local Disk',
            self::S3    => 'S3-Compatible',
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
