<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Laboratory\DTO\CreateLaboratoryDTO;
use App\Domains\Laboratory\DTO\UpdateLaboratoryDTO;
use App\Domains\Laboratory\Interfaces\LaboratoryServiceInterface;
use App\Domains\Laboratory\Models\Laboratory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('id')->andReturn(null);
    Auth::shouldReceive('user')->andReturn(null);

    $this->orgId     = (string) Str::orderedUuid();
    $this->branchId  = (string) Str::orderedUuid();
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-LAB-01', 'company_name' => 'Lab Test Org',
        'email' => 'lab@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-LAB-01',
        'branch_name' => 'Lab Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-LAB-01',
        'full_name' => 'Lab Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(LaboratoryServiceInterface::class);
});

it('creates lab order from DTO and returns Laboratory model', function (): void {
    $dto = new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-001',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    );

    $lab = $this->service->create($dto);

    expect($lab)->toBeInstanceOf(Laboratory::class);
    expect($lab->order_number)->toBe('LAB-001');
    expect($lab->patient_id)->toBe($this->patientId);
    expect($lab->organization_id)->toBe($this->orgId);
    expect($lab->status)->toBe('pending');
    expect($lab->ordered_at->format('Y-m-d'))->toBe('2026-08-17');
});

it('throws BusinessException when status transition is invalid (completed → in_progress)', function (): void {
    $created = $this->service->create(new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-002',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    ));

    $this->service->update($created->id, new UpdateLaboratoryDTO(
        status: 'in_progress',
    ), $this->orgId);

    $this->service->update($created->id, new UpdateLaboratoryDTO(
        status: 'completed',
    ), $this->orgId);

    expect(fn () => $this->service->update($created->id, new UpdateLaboratoryDTO(
        status: 'in_progress',
    ), $this->orgId))->toThrow(BusinessException::class);
});

it('finds lab order by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-003',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->order_number)->toBe('LAB-003');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-004',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates lab orders scoped to organization', function (): void {
    $this->service->create(new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-005',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    ));
    $this->service->create(new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-006',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates lab order from DTO', function (): void {
    $created = $this->service->create(new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-007',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    ));

    $updated = $this->service->update($created->id, new UpdateLaboratoryDTO(
        description: 'Updated description',
        notes: 'Updated notes',
        orderedAt: '2026-08-18',
    ), $this->orgId);

    expect($updated->description)->toBe('Updated description');
    expect($updated->notes)->toBe('Updated notes');
    expect($updated->ordered_at->format('Y-m-d'))->toBe('2026-08-18');
});

it('soft-deletes lab order', function (): void {
    $created = $this->service->create(new CreateLaboratoryDTO(
        patientId: $this->patientId,
        orderNumber: 'LAB-008',
        organizationId: $this->orgId,
        orderedAt: '2026-08-17',
        doctorId: null,
        categoryId: null,
        description: null,
        results: null,
        notes: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Laboratory::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});