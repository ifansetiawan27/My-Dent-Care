<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Inventory\DTO\CreateInventoryDTO;
use App\Domains\Inventory\DTO\UpdateInventoryDTO;
use App\Domains\Inventory\Interfaces\InventoryServiceInterface;
use App\Domains\Inventory\Models\Inventory;
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
        'id' => $this->orgId, 'company_code' => 'ORG-INV-01', 'company_name' => 'Inventory Test Org',
        'email' => 'inv@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-INV-01',
        'branch_name' => 'Inventory Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(InventoryServiceInterface::class);
});

it('creates inventory item from DTO and returns Inventory model', function (): void {
    $dto = new CreateInventoryDTO(
        itemCode: 'ITM-001',
        name: 'Dental Chair',
        unit: 'unit',
        organizationId: $this->orgId,
        branchId: $this->branchId,
        categoryId: null,
        description: null,
        quantity: null,
        minQuantity: null,
        unitPrice: null,
    );

    $inventory = $this->service->create($dto);

    expect($inventory)->toBeInstanceOf(Inventory::class);
    expect($inventory->item_code)->toBe('ITM-001');
    expect($inventory->name)->toBe('Dental Chair');
    expect($inventory->organization_id)->toBe($this->orgId);
    expect($inventory->is_active)->toBeTrue();
});

it('throws BusinessException when item_code already exists', function (): void {
    $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-001', name: 'First', unit: 'unit',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));

    expect(fn () => $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-001', name: 'Second', unit: 'unit',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    )))->toThrow(BusinessException::class, 'Item code already exists.');
});

it('finds inventory item by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-002', name: 'Gloves', unit: 'box',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->name)->toBe('Gloves');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-003', name: 'Mask', unit: 'box',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates inventory items scoped to organization', function (): void {
    $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-004', name: 'Item A', unit: 'unit',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));
    $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-005', name: 'Item B', unit: 'unit',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates inventory item from DTO', function (): void {
    $created = $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-006', name: 'Original', unit: 'box',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));

    $updated = $this->service->update($created->id, new UpdateInventoryDTO(
        itemCode: null, name: 'Updated Name', unit: null, branchId: null,
        categoryId: null, description: 'New desc', quantity: '50',
        minQuantity: null, unitPrice: '25000', isActive: null,
    ), $this->orgId);

    expect($updated->name)->toBe('Updated Name');
    expect($updated->description)->toBe('New desc');
    expect($updated->quantity)->toBe('50.00');
    expect($updated->unit_price)->toBe('25000.00');
});

it('soft-deletes inventory item', function (): void {
    $created = $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-007', name: 'Deletable', unit: 'unit',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Inventory::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});

it('toggleActive flips is_active', function (): void {
    $created = $this->service->create(new CreateInventoryDTO(
        itemCode: 'ITM-008', name: 'Toggle', unit: 'unit',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, quantity: null, minQuantity: null, unitPrice: null,
    ));

    expect($created->is_active)->toBeTrue();

    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->is_active)->toBeFalse();

    $toggledAgain = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggledAgain->is_active)->toBeTrue();
});