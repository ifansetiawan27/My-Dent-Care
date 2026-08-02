<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\DTO;

use App\Platform\FileStorage\Enums\StorageDriver;
use App\Platform\FileStorage\Enums\StorageFolder;

/**
 * StoredFileDTO
 *
 * Immutable value object returned after a file is successfully stored.
 * Represents the persisted file metadata — the physical file is always
 * named with a UUID, never the user's original filename.
 */
final readonly class StoredFileDTO
{
    /**
     * @param  string        $id            UUID — also the stored file name.
     * @param  StorageFolder $folder        Category folder.
     * @param  StorageDriver $disk          Storage backend used.
     * @param  string        $path          Full path within the disk.
     * @param  string        $originalName  Original filename (metadata only).
     * @param  string        $storedName    UUID-based physical filename.
     * @param  string        $mimeType      MIME type.
     * @param  string        $extension     File extension.
     * @param  int           $size          Size in bytes.
     * @param  string        $hash          SHA-256 integrity hash.
     * @param  string|null   $organizationId Tenant organization UUID.
     * @param  string|null   $branchId       Tenant branch UUID.
     */
    public function __construct(
        public string        $id,
        public StorageFolder $folder,
        public StorageDriver $disk,
        public string        $path,
        public string        $originalName,
        public string        $storedName,
        public string        $mimeType,
        public string        $extension,
        public int           $size,
        public string        $hash,
        public ?string       $organizationId = null,
        public ?string       $branchId       = null,
    ) {}

    /**
     * Serialize to array for persistence or API response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'folder'          => $this->folder->value,
            'disk'            => $this->disk->value,
            'path'            => $this->path,
            'original_name'   => $this->originalName,
            'stored_name'     => $this->storedName,
            'mime_type'       => $this->mimeType,
            'extension'       => $this->extension,
            'size'            => $this->size,
            'hash'            => $this->hash,
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
        ];
    }
}
