<?php

declare(strict_types=1);

use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use App\Platform\FileStorage\DTO\StoredFileDTO;
use App\Platform\FileStorage\Enums\StorageFolder;
use App\Platform\FileStorage\Models\File;
use App\Platform\FileStorage\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('user')->andReturn(null);
    Storage::fake('local');

    $mockAudit = mock(AuditServiceInterface::class);
    $mockAudit->shouldReceive('log')->byDefault();
    app()->instance(AuditServiceInterface::class, $mockAudit);
    app()->bind(FileStorageServiceInterface::class, fn () => app(FileStorageService::class));

    $this->organizationId = (string) Str::orderedUuid();
    $this->branchId       = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id'           => $this->organizationId,
        'company_code' => 'ORG-FS-01',
        'company_name' => 'FileStorage Test Organization',
        'email'        => 'fs-test@example.com',
        'phone'        => '081234567890',
        'address'      => 'Jl. Test 1',
        'city'         => 'Jakarta',
        'province'     => 'DKI Jakarta',
        'postal_code'  => '12345',
        'created_at'   => $now,
        'updated_at'   => $now,
    ]);

    DB::table('branches')->insert([
        'id'              => $this->branchId,
        'organization_id' => $this->organizationId,
        'branch_code'     => 'BRC-FS-01',
        'branch_name'     => 'FileStorage Test Clinic',
        'branch_type'     => 'clinic',
        'phone'           => '081234567891',
        'address'         => 'Jl. Test 2',
        'city'            => 'Jakarta',
        'province'        => 'DKI Jakarta',
        'postal_code'     => '12345',
        'created_at'      => $now,
        'updated_at'      => $now,
    ]);
});

it('persists file metadata after store', function (): void {
    $mockAudit = mock(AuditServiceInterface::class);
    $mockAudit->shouldReceive('log')->once();
    app()->instance(AuditServiceInterface::class, $mockAudit);
    app()->bind(FileStorageServiceInterface::class, fn () => app(FileStorageService::class));

    $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId, $this->branchId);

    $record = File::find($dto->id);
    expect($record)->not->toBeNull();
    expect($record->organization_id)->toBe($this->organizationId);
    expect($record->branch_id)->toBe($this->branchId);
    expect($record->folder)->toBe('patient');
    expect($record->original_name)->toBe('photo.jpg');
    expect($record->stored_name)->toBe($dto->id);
    expect($record->mime_type)->toBe('image/jpeg');
    expect($record->extension)->toBe('jpg');
    expect($record->size)->toBeGreaterThan(0);
    expect($record->hash)->toHaveLength(64);
    expect($record->disk)->toBe('local');
    expect($record->path)->toContain("patient/{$this->organizationId}/{$this->branchId}");
});

it('file metadata matches DTO after store', function (): void {
    $mockAudit = mock(AuditServiceInterface::class);
    $mockAudit->shouldReceive('log')->once();
    app()->instance(AuditServiceInterface::class, $mockAudit);
    app()->bind(FileStorageServiceInterface::class, fn () => app(FileStorageService::class));

    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Doctor, $this->organizationId, null);

    $record = File::find($dto->id);

    expect($record->id)->toBe($dto->id);
    expect($record->folder)->toBe($dto->folder->value);
    expect($record->original_name)->toBe($dto->originalName);
    expect($record->stored_name)->toBe($dto->storedName);
    expect($record->mime_type)->toBe($dto->mimeType);
    expect($record->extension)->toBe($dto->extension);
    expect($record->size)->toBe($dto->size);
    expect($record->hash)->toBe($dto->hash);
    expect($record->organization_id)->toBe($dto->organizationId);
    expect($record->branch_id)->toBe($dto->branchId);
    expect($record->path)->toBe($dto->path);
});

it('soft-deletes file via BaseModel SoftDeletes trait', function (): void {
    $mockAudit = mock(AuditServiceInterface::class);
    $mockAudit->shouldReceive('log')->twice();
    app()->instance(AuditServiceInterface::class, $mockAudit);
    app()->bind(FileStorageServiceInterface::class, fn () => app(FileStorageService::class));

    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId);

    app(FileStorageServiceInterface::class)->delete($dto->path);

    $record = File::withTrashed()->find($dto->id);
    expect($record->deleted_at)->not->toBeNull();

    $active = File::find($dto->id);
    expect($active)->toBeNull();
});

it('audit records are created for upload and delete', function (): void {
    $mockAudit = mock(AuditServiceInterface::class);
    $mockAudit->shouldReceive('log')->twice();
    app()->instance(AuditServiceInterface::class, $mockAudit);
    app()->bind(FileStorageServiceInterface::class, fn () => app(FileStorageService::class));

    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId);
    app(FileStorageServiceInterface::class)->delete($dto->path);

    $mockAudit->shouldHaveReceived('log')->twice();
});

it('StoredFileDTO is readonly immutable value object', function (): void {
    $reflection = new ReflectionClass(StoredFileDTO::class);

    expect($reflection->isReadOnly())->toBeTrue();
});
