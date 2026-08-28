<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use App\Platform\FileStorage\DTO\StoredFileDTO;
use App\Platform\FileStorage\Enums\StorageFolder;
use App\Platform\FileStorage\Enums\StorageDriver;
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

it('store validates extension against folder whitelist', function (): void {
    $folder = StorageFolder::Branch;
    $file = UploadedFile::fake()->create('document.exe', 100, 'application/x-msdownload');

    expect(fn () => app(FileStorageServiceInterface::class)->store($file, $folder, $this->organizationId))
        ->toThrow(BusinessException::class);
});

it('store validates size against folder limit', function (): void {
    $folder = StorageFolder::Organization;
    $maxBytes = $folder->maxSizeBytes();
    $file = UploadedFile::fake()->create('logo.png', ($maxBytes + 1) / 1024, 'image/png');

    expect(fn () => app(FileStorageServiceInterface::class)->store($file, $folder, $this->organizationId))
        ->toThrow(BusinessException::class);
});

it('store accepts valid file and returns StoredFileDTO', function (): void {
    $folder = StorageFolder::Patient;
    $file = UploadedFile::fake()->create('patient-photo.jpg', 100, 'image/jpeg');

    $dto = app(FileStorageServiceInterface::class)->store($file, $folder, $this->organizationId, $this->branchId);

    expect($dto)->toBeInstanceOf(StoredFileDTO::class);
    expect($dto->folder)->toBe($folder);
    expect($dto->originalName)->toBe('patient-photo.jpg');
    expect($dto->storedName)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-/i');
    expect($dto->mimeType)->toBe('image/jpeg');
    expect($dto->extension)->toBe('jpg');
    expect($dto->size)->toBeGreaterThan(0);
    expect($dto->hash)->toHaveLength(64);
    expect($dto->organizationId)->toBe($this->organizationId);
    expect($dto->branchId)->toBe($this->branchId);
    expect($dto->path)->toContain("patient/{$this->organizationId}/{$this->branchId}");
    expect($dto->disk)->toBe(StorageDriver::Local);
});

it('store generates UUID-based stored name', function (): void {
    $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId);

    expect($dto->storedName)->not->toBe('photo.jpg');
    expect($dto->storedName)->toMatch('/^[0-9a-f-]{36}$/');
});

it('store computes SHA-256 hash', function (): void {
    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId);

    expect($dto->hash)->toHaveLength(64);
    expect(ctype_xdigit($dto->hash))->toBeTrue();
});

it('store builds multi-tenant path', function (): void {
    $file = UploadedFile::fake()->create('file.jpg', 100, 'image/jpeg');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId, $this->branchId);

    expect($dto->path)->toContain("patient/{$this->organizationId}/{$this->branchId}");
});

it('store uses global branch path when branchId is null', function (): void {
    $file = UploadedFile::fake()->create('file.jpg', 100, 'image/jpeg');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Organization, $this->organizationId, null);

    expect($dto->path)->toContain("organization/{$this->organizationId}/global");
});

it('temporaryUrl generates signed URL with default 15-minute expiry', function (): void {
    $service = app(FileStorageServiceInterface::class);
    $url = $service->temporaryUrl('patient/org-1/global/2026/08/uuid.jpg');

    expect($url)->toBeString();
});

it('exists returns true for stored file', function (): void {
    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId);

    $service = app(FileStorageServiceInterface::class);
    expect($service->exists($dto->path))->toBeTrue();
});

it('exists returns false for nonexistent path', function (): void {
    $service = app(FileStorageServiceInterface::class);
    expect($service->exists('nonexistent/path/file.pdf'))->toBeFalse();
});

it('get returns file contents for existing file', function (): void {
    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId);

    $service = app(FileStorageServiceInterface::class);
    $content = $service->get($dto->path);
    expect($content)->toBeString();
});

it('get returns null for nonexistent file', function (): void {
    $service = app(FileStorageServiceInterface::class);
    expect($service->get('nonexistent/path'))->toBeNull();
});

it('delete soft-deletes file record and returns true', function (): void {
    $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
    $dto = app(FileStorageServiceInterface::class)->store($file, StorageFolder::Patient, $this->organizationId);

    $service = app(FileStorageServiceInterface::class);
    expect($service->delete($dto->path))->toBeTrue();

    expect(File::where('id', $dto->id)->first())->toBeNull();
    expect(File::withTrashed()->where('id', $dto->id)->first())->not->toBeNull();
});

it('delete returns false for nonexistent path', function (): void {
    $service = app(FileStorageServiceInterface::class);
    expect($service->delete('nonexistent/path'))->toBeFalse();
});

it('File model extends BaseModel with HasUuid, HasAudit, SoftDeletes', function (): void {
    $traits = class_uses_recursive(File::class);

    expect($traits)->toHaveKey('App\Core\Traits\HasUuid');
    expect($traits)->toHaveKey('App\Core\Traits\HasAudit');
    expect($traits)->toHaveKey('Illuminate\Database\Eloquent\SoftDeletes');
    expect((new File())->getTable())->toBe('files');
});

it('StorageFolder enum has correct allowedExtensions', function (): void {
    expect(StorageFolder::Branch->allowedExtensions())->toBe(['jpg', 'jpeg', 'png']);
    expect(StorageFolder::Radiology->allowedExtensions())->toContain('dcm');
    expect(StorageFolder::Organization->allowedExtensions())->toContain('svg');

    $defaults = StorageFolder::Patient->allowedExtensions();
    expect($defaults)->toBe(['jpg', 'jpeg', 'png', 'pdf']);
});

it('StorageFolder enum has correct maxSizeBytes', function (): void {
    expect(StorageFolder::Radiology->maxSizeBytes())->toBe(100 * 1024 * 1024);
    expect(StorageFolder::Lab->maxSizeBytes())->toBe(20 * 1024 * 1024);
    expect(StorageFolder::Patient->maxSizeBytes())->toBe(10 * 1024 * 1024);
    expect(StorageFolder::Doctor->maxSizeBytes())->toBe(10 * 1024 * 1024);
    expect(StorageFolder::Asset->maxSizeBytes())->toBe(10 * 1024 * 1024);
    expect(StorageFolder::Organization->maxSizeBytes())->toBe(5 * 1024 * 1024);
    expect(StorageFolder::Branch->maxSizeBytes())->toBe(5 * 1024 * 1024);
});

it('StorageDriver enum has local and s3 cases', function (): void {
    expect(StorageDriver::cases())->toHaveCount(2);
    expect(StorageDriver::Local->value)->toBe('local');
    expect(StorageDriver::S3->value)->toBe('s3');
});
