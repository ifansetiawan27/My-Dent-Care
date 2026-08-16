<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Services;

use App\Core\Exceptions\BusinessException;
use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\Audit\Enums\AuditAction;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use App\Platform\FileStorage\DTO\StoredFileDTO;
use App\Platform\FileStorage\Enums\StorageDriver;
use App\Platform\FileStorage\Enums\StorageFolder;
use App\Platform\FileStorage\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class FileStorageService implements FileStorageServiceInterface
{
    public function __construct(
        private readonly AuditServiceInterface $audit,
    ) {}

    public function store(
        UploadedFile  $file,
        StorageFolder $folder,
        ?string       $organizationId = null,
        ?string       $branchId       = null,
    ): StoredFileDTO {
        $extension     = strtolower((string) $file->getClientOriginalExtension());
        $mimeType      = (string) $file->getMimeType();
        $size          = $file->getSize();

        $allowedExtensions = $folder->allowedExtensions();
        if (! in_array($extension, $allowedExtensions, true)) {
            throw new BusinessException("File extension '{$extension}' is not allowed for folder '{$folder->value}'.");
        }

        if ($size > $folder->maxSizeBytes()) {
            $maxMB = $folder->maxSizeBytes() / (1024 * 1024);
            throw new BusinessException("File size exceeds the maximum of {$maxMB} MB for folder '{$folder->value}'.");
        }

        $uuid       = File::newUuid();
        $hash       = hash_file('sha256', $file->getRealPath());
        $now        = now();
        $yearMonth  = $now->format('Y/m');
        $branchPath = $branchId ?? 'global';
        $path       = \sprintf('%s/%s/%s/%s/%s.%s', $folder->value, $organizationId, $branchPath, $yearMonth, $uuid, $extension);

        if (! Storage::disk($this->activeDisk())->put($path, $file->getContent())) {
            throw new BusinessException('Failed to write file to storage.');
        }

        $stored = File::create([
            'id'              => $uuid,
            'organization_id' => $organizationId,
            'branch_id'       => $branchId,
            'folder'          => $folder->value,
            'disk'            => $this->activeDisk(),
            'path'            => $path,
            'original_name'   => $file->getClientOriginalName(),
            'stored_name'     => $uuid,
            'mime_type'       => $mimeType,
            'extension'       => $extension,
            'size'            => $size,
            'hash'            => $hash,
        ]);

        $this->audit->log(
            action:        AuditAction::Create,
            module:        'filestorage',
            auditableType: File::class,
            auditableId:   $stored->id,
            oldValue:      [],
            newValue:      ['path' => $path, 'folder' => $folder->value],
        );

        return new StoredFileDTO(
            id:             $stored->id,
            folder:         $folder,
            disk:           StorageDriver::from($stored->disk),
            path:           $stored->path,
            originalName:   $stored->original_name,
            storedName:     $stored->stored_name,
            mimeType:       $stored->mime_type,
            extension:      $stored->extension,
            size:           $stored->size,
            hash:           $stored->hash,
            organizationId: $stored->organization_id,
            branchId:       $stored->branch_id,
        );
    }

    public function temporaryUrl(string $path, int $expiresIn = 900): string
    {
        $disk = Storage::disk($this->activeDisk());

        if (! $disk->providesTemporaryUrls()) {
            return $disk->url($path);
        }

        return $disk->temporaryUrl($path, now()->addSeconds($expiresIn));
    }

    public function get(string $path): ?string
    {
        $disk = $this->activeDisk();

        return Storage::disk($disk)->exists($path) ? Storage::disk($disk)->get($path) : null;
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->activeDisk())->exists($path);
    }

    public function delete(string $path): bool
    {
        $file = File::where('path', $path)->first();

        if (! $file) {
            return false;
        }

        $deleted = (bool) $file->delete();

        if ($deleted) {
            $this->audit->log(
                action:        AuditAction::Delete,
                module:        'filestorage',
                auditableType: File::class,
                auditableId:   $file->id,
                oldValue:      ['path' => $path],
                newValue:      [],
            );
        }

        return $deleted;
    }

    private function activeDisk(): string
    {
        return config('filesystems.default', 'local');
    }
}
