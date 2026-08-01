<?php

declare(strict_types=1);

namespace App\Core\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Initialize the HasUuid trait for an instance.
     * Sets key type and disables auto-increment at the instance level.
     */
    public function initializeHasUuid(): void
    {
        $this->keyType      = 'string';
        $this->incrementing = false;
    }

    /**
     * Boot the HasUuid trait.
     * Automatically generates an ordered UUID before model creation.
     */
    public static function bootHasUuid(): void
    {
        static::creating(function (self $model): void {
            $key = $model->getKeyName();

            if (empty($model->{$key})) {
                $model->{$key} = (string) Str::orderedUuid();
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing ID type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Generate a new ordered UUID string.
     * Useful for manually assigning UUIDs before persisting.
     */
    public static function newUuid(): string
    {
        return (string) Str::orderedUuid();
    }
}
