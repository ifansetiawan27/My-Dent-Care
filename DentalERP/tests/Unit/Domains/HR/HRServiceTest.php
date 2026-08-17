<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\HR\DTO\CreateHRDTO;
use App\Domains\HR\DTO\UpdateHRDTO;
use App\Domains\HR\Interfaces\HRServiceInterface;
use App\Domains\HR\Models\HR;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('id')->andReturn(null);
    Auth::shouldReceive('user')->andReturn(null);

    $this->orgId    = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-HR-01', 'company_name' => 'HR Test Org',
        'email' => 'hr@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-HR-01',
        'branch_name' => 'HR Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(HRServiceInterface::class);
});

it('creates HR record from DTO and returns HR model', function (): void {
    $dto = new CreateHRDTO(
        recordType: 'employment',
        effectiveDate: '2026-08-17',
        organizationId: $this->orgId,
        employeeId: null,
        endDate: null,
        data: null,
        notes: null,
    );

    $hr = $this->service->create($dto);

    expect($hr)->toBeInstanceOf(HR::class);
    expect($hr->record_type)->toBe('employment');
    expect($hr->organization_id)->toBe($this->orgId);
    expect($hr->status)->toBe('active');
});

it('throws BusinessException when status transition is invalid (archived → active)', function (): void {
    $created = $this->service->create(new CreateHRDTO(
        recordType: 'employment', effectiveDate: '2026-08-17',
        organizationId: $this->orgId, employeeId: null,
        endDate: null, data: null, notes: null,
    ));

    $this->service->update($created->id, new UpdateHRDTO(
        status: 'archived',
    ), $this->orgId);

    expect(fn () => $this->service->update($created->id, new UpdateHRDTO(
        status: 'active',
    ), $this->orgId))->toThrow(BusinessException::class);
});

it('finds HR record by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateHRDTO(
        recordType: 'employment', effectiveDate: '2026-08-17',
        organizationId: $this->orgId, employeeId: null,
        endDate: null, data: null, notes: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->record_type)->toBe('employment');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateHRDTO(
        recordType: 'employment', effectiveDate: '2026-08-17',
        organizationId: $this->orgId, employeeId: null,
        endDate: null, data: null, notes: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates HR records scoped to organization', function (): void {
    $this->service->create(new CreateHRDTO(
        recordType: 'employment', effectiveDate: '2026-08-17',
        organizationId: $this->orgId, employeeId: null,
        endDate: null, data: null, notes: null,
    ));
    $this->service->create(new CreateHRDTO(
        recordType: 'promotion', effectiveDate: '2026-08-17',
        organizationId: $this->orgId, employeeId: null,
        endDate: null, data: null, notes: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates HR record from DTO', function (): void {
    $created = $this->service->create(new CreateHRDTO(
        recordType: 'employment', effectiveDate: '2026-08-17',
        organizationId: $this->orgId, employeeId: null,
        endDate: null, data: null, notes: null,
    ));

    $updated = $this->service->update($created->id, new UpdateHRDTO(
        recordType: 'promotion',
        status: 'inactive',
        notes: 'Updated notes',
    ), $this->orgId);

    expect($updated->record_type)->toBe('promotion');
    expect($updated->status)->toBe('inactive');
    expect($updated->notes)->toBe('Updated notes');
});

it('soft-deletes HR record', function (): void {
    $created = $this->service->create(new CreateHRDTO(
        recordType: 'employment', effectiveDate: '2026-08-17',
        organizationId: $this->orgId, employeeId: null,
        endDate: null, data: null, notes: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(HR::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});