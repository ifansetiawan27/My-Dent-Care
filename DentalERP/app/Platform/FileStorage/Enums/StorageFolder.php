<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Enums;

/**
 * StorageFolder
 *
 * Top-level storage categories. Every stored file belongs to exactly one folder.
 * Path convention: {folder}/{organization_id}/{branch_id}/{yyyy}/{mm}/{uuid}.{ext}
 */
enum StorageFolder: string
{
    case Patient      = 'patient';
    case Doctor       = 'doctor';
    case Organization = 'organization';
    case Branch       = 'branch';
    case Lab          = 'lab';
    case Radiology    = 'radiology';
    case Asset        = 'asset';

    /**
     * Maximum allowed file size in bytes for this folder.
     */
    public function maxSizeBytes(): int
    {
        return match ($this) {
            self::Radiology => 100 * 1024 * 1024, // 100 MB
            self::Lab       => 20 * 1024 * 1024,  // 20 MB
            self::Patient,
            self::Doctor,
            self::Asset     => 10 * 1024 * 1024,  // 10 MB
            self::Organization,
            self::Branch    => 5 * 1024 * 1024,   // 5 MB
        };
    }

    /**
     * Allowed file extensions (whitelist) for this folder.
     *
     * @return array<string>
     */
    public function allowedExtensions(): array
    {
        return match ($this) {
            self::Radiology   => ['dcm', 'jpg', 'jpeg', 'png', 'pdf'],
            self::Lab         => ['pdf', 'jpg', 'jpeg', 'png'],
            self::Organization => ['jpg', 'jpeg', 'png', 'svg', 'pdf'],
            self::Branch      => ['jpg', 'jpeg', 'png'],
            default           => ['jpg', 'jpeg', 'png', 'pdf'],
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
