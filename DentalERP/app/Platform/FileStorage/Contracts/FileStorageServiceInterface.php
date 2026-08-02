<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Contracts;

use App\Platform\FileStorage\DTO\StoredFileDTO;
use App\Platform\FileStorage\Enums\StorageFolder;
use Illuminate\Http\UploadedFile;

/**
 * FileStorageServiceInterface
 *
 * The single contract for file storage across the entire ERP.
 * Implementations validate (MIME + size), assign a UUID name, build a
 * multi-tenant path, compute a SHA-256 hash, and persist to the active disk.
 *
 * Platform rule: Domains depend on this interface only — never on
 * Storage::put() or the S3 SDK directly. Files are ALWAYS named with a UUID.
 */
interface FileStorageServiceInterface
{
    /**
     * Validate and store an uploaded file.
     *
     * @param  UploadedFile        $file            The uploaded file.
     * @param  StorageFolder       $folder          Target category folder.
     * @param  string|null         $organizationId  Tenant organization UUID.
     * @param  string|null         $branchId        Tenant branch UUID.
     * @return StoredFileDTO
     *
     * @throws \App\Core\Exceptions\BusinessException  When MIME/size validation fails.
     */
    public function store(
        UploadedFile  $file,
        StorageFolder $folder,
        ?string       $organizationId = null,
        ?string       $branchId       = null,
    ): StoredFileDTO;

    /**
     * Generate a temporary signed URL for accessing a stored file.
     *
     * @param  string $path        Full storage path.
     * @param  int    $expiresIn   Expiry in seconds (default 900 = 15 min).
     * @return string
     */
    public function temporaryUrl(string $path, int $expiresIn = 900): string;

    /**
     * Retrieve raw file contents.
     *
     * @param  string      $path
     * @return string|null
     */
    public function get(string $path): ?string;

    /**
     * Determine whether a file exists at the given path.
     */
    public function exists(string $path): bool;

    /**
     * Delete a file from storage.
     *
     * @param  string $path
     * @return bool
     */
    public function delete(string $path): bool;
}
