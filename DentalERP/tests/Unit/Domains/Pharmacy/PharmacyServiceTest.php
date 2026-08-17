<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Pharmacy\DTO\CreatePharmacyDTO;
use App\Domains\Pharmacy\DTO\UpdatePharmacyDTO;
use App\Domains\Pharmacy\Interfaces\PharmacyServiceInterface;
use App\Domains\Pharmacy\Models\Pharmacy;
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
        'id' => $this->orgId, 'company_code' => 'ORG-PHARM-01', 'company_name' => 'Pharmacy Test Org',
        'email' => 'pharm@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-PHARM-01',
        'branch_name' => 'Pharmacy Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(PharmacyServiceInterface::class);
});

it('creates pharmacy item from DTO and returns Pharmacy model', function (): void {
    $dto = new CreatePharmacyDTO(
        drugCode: 'DRG-001',
        name: 'Amoxicillin 500mg',
        organizationId: $this->orgId,
        branchId: $this->branchId,
        category: 'Antibiotic',
        quantity: null,
        unit: 'tablet',
        unitPrice: null,
        expiryDate: null,
        batchNumber: null,
    );

    $pharmacy = $this->service->create($dto);

    expect($pharmacy)->toBeInstanceOf(Pharmacy::class);
    expect($pharmacy->drug_code)->toBe('DRG-001');
    expect($pharmacy->name)->toBe('Amoxicillin 500mg');
    expect($pharmacy->organization_id)->toBe($this->orgId);
    expect($pharmacy->is_active)->toBeTrue();
});

it('throws BusinessException when drug_code already exists', function (): void {
    $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-001', name: 'First', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));

    expect(fn () => $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-001', name: 'Second', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    )))->toThrow(BusinessException::class, 'Drug code already exists.');
});

it('finds pharmacy item by id scoped to organization', function (): void {
    $created = $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-002', name: 'Paracetamol', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->name)->toBe('Paracetamol');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-003', name: 'Ibuprofen', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates pharmacy items scoped to organization', function (): void {
    $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-004', name: 'Item A', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));
    $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-005', name: 'Item B', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates pharmacy item from DTO', function (): void {
    $created = $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-006', name: 'Original', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));

    $updated = $this->service->update($created->id, new UpdatePharmacyDTO(
        drugCode: null, name: 'Updated Name', branchId: null,
        category: 'Painkiller', quantity: '100', unit: 'tablet',
        unitPrice: '5000', expiryDate: null, batchNumber: 'BATCH-001',
        isActive: null,
    ), $this->orgId);

    expect($updated->name)->toBe('Updated Name');
    expect($updated->category)->toBe('Painkiller');
    expect($updated->quantity)->toBe('100.00');
    expect($updated->unit_price)->toBe('5000.00');
    expect($updated->batch_number)->toBe('BATCH-001');
});

it('soft-deletes pharmacy item', function (): void {
    $created = $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-007', name: 'Deletable', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Pharmacy::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});

it('toggleActive flips is_active', function (): void {
    $created = $this->service->create(new CreatePharmacyDTO(
        drugCode: 'DRG-008', name: 'Toggle', organizationId: $this->orgId,
        branchId: null, category: null, quantity: null, unit: null,
        unitPrice: null, expiryDate: null, batchNumber: null,
    ));

    expect($created->is_active)->toBeTrue();

    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->is_active)->toBeFalse();

    $toggledAgain = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggledAgain->is_active)->toBeTrue();
});