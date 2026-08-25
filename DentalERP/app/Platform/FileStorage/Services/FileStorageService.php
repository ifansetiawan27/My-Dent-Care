<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Services;

use App\Core\Exceptions\BusinessException;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use App\Platform\FileStorage\DTO\StoredFileDTO;
use App\Platform\FileStorage\Enums\StorageDriver;
use App\Platform\FileStorage\Enums\StorageFolder;
use App\Platform\FileStorage\Models\File;
use App\Platform\FileStorage\Repositories\FileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * FileStorageService
 *
 * Concrete implementation of FileStorageServiceInterface.
 * Validates, hashes, and stores files with UUID names in multi-tenant paths.
 */
final class FileStorageService implements FileStorageServiceInterface
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB
    
    private const ALLOWED_MIMES = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function __construct(
        private readonly FileRepository $repository
    ) {
    }

    public function store(
        UploadedFile  $file,
        StorageFolder $folder,
        ?string       $organizationId = null,
        ?string       $branchId       = null,
    ): StoredFileDTO {
        $this->validate($file);

        $user = Auth::user();
        $organizationId = $organizationId ?? $user?->organization_id;
        $branchId = $branchId ?? $user?->branch_id;

        if (!$organizationId) {
            throw new BusinessException('Organization ID is required for file storage');
        }

        // Generate UUID for file
        $fileId = (string) Str::orderedUuid();
        $extension = $file->getClientOriginalExtension();
        $storedName = "{$fileId}.{$extension}";
        
        // Build multi-tenant path
        $yearMonth = now()->format('Y/m');
        $path = "{$folder->value}/{$organizationId}/{$branchId}/{$yearMonth}/{$storedName}";
        
        // Compute hash
        $realPath = $file->getRealPath();
        if ($realPath === false) {
            throw new BusinessException('Unable to read uploaded file.');
        }
        $hash = hash_file('sha256', $realPath);
        if ($hash === false) {
            throw new BusinessException('Unable to compute file hash.');
        }
        
        // Check for duplicate
        $duplicate = $this->repository->findByHash($hash, $organizationId);
        if ($duplicate) {
            return new StoredFileDTO(
                id: $duplicate->id,
                folder: StorageFolder::from($duplicate->folder),
                disk: StorageDriver::from($duplicate->disk),
                path: $duplicate->path,
                originalName: $file->getClientOriginalName(),
                storedName: $duplicate->stored_name,
                mimeType: $duplicate->mime_type,
                extension: $duplicate->extension,
                size: $duplicate->size,
                hash: $duplicate->hash,
                organizationId: $duplicate->organization_id,
                branchId: $duplicate->branch_id,
            );
        }

        // Store file
        $disk = config('filesystems.default', 'local');
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        // Persist metadata
        $fileRecord = $this->repository->create([
            'id' => $fileId,
            'organization_id' => $organizationId,
            'branch_id' => $branchId,
            'folder' => $folder->value,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size' => $file->getSize(),
            'hash' => $hash,
            'created_by' => $user?->id,
        ]);

        return new StoredFileDTO(
            id: $fileRecord->id,
            folder: StorageFolder::from($fileRecord->folder),
            disk: StorageDriver::from($fileRecord->disk),
            path: $fileRecord->path,
            originalName: $fileRecord->original_name,
            storedName: $fileRecord->stored_name,
            mimeType: $fileRecord->mime_type,
            extension: $fileRecord->extension,
            size: $fileRecord->size,
            hash: $fileRecord->hash,
            organizationId: $fileRecord->organization_id,
            branchId: $fileRecord->branch_id,
        );
    }

    public function temporaryUrl(string $path, int $expiresIn = 900): string
    {
        $disk = config('filesystems.default', 'local');
        
        if ($disk === 'local') {
            return url("storage/{$path}");
        }

        return Storage::disk($disk)->temporaryUrl($path, now()->addSeconds($expiresIn));
    }

    public function get(string $path): ?string
    {
        $disk = config('filesystems.default', 'local');
        
        if (!$this->exists($path)) {
            return null;
        }

        return Storage::disk($disk)->get($path);
    }

    public function exists(string $path): bool
    {
        $disk = config('filesystems.default', 'local');
        return Storage::disk($disk)->exists($path);
    }

    public function delete(string $path): bool
    {
        $disk = config('filesystems.default', 'local');
        return Storage::disk($disk)->delete($path);
    }

    private function validate(UploadedFile $file): void
    {
        // Size validation
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new BusinessException(
                'File size exceeds maximum allowed size of ' . (self::MAX_FILE_SIZE / 1024 / 1024) . ' MB'
            );
        }

        // MIME type validation
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new BusinessException(
                'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_MIMES)
            );
        }
    }
}
