<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Procurement\DTO\CreateProcurementDTO;
use App\Domains\Procurement\DTO\UpdateProcurementDTO;
use App\Domains\Procurement\Interfaces\ProcurementServiceInterface;
use App\Domains\Procurement\Models\Procurement;
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
        'id' => $this->orgId, 'company_code' => 'ORG-PROC-01', 'company_name' => 'Procurement Test Org',
        'email' => 'proc@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-PROC-01',
        'branch_name' => 'Procurement Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(ProcurementServiceInterface::class);
});

it('creates procurement order from DTO and returns Procurement model', function (): void {
    $dto = new CreateProcurementDTO(
        orderNumber: 'PO-001',
        orderDate: '2026-08-17',
        organizationId: $this->orgId,
        supplierId: null,
        branchId: $this->branchId,
        expectedDate: null,
        totalAmount: null,
        items: null,
        notes: null,
    );

    $procurement = $this->service->create($dto);

    expect($procurement)->toBeInstanceOf(Procurement::class);
    expect($procurement->order_number)->toBe('PO-001');
    expect($procurement->organization_id)->toBe($this->orgId);
    expect($procurement->status)->toBe('pending');
});

it('throws BusinessException when order_number already exists', function (): void {
    $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-001', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));

    expect(fn () => $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-001', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    )))->toThrow(BusinessException::class, 'Order number already exists.');
});

it('throws BusinessException when status transition is invalid (completed → in_progress)', function (): void {
    $created = $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-002', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));

    $this->service->update($created->id, new UpdateProcurementDTO(
        status: 'approved',
    ), $this->orgId);

    $this->service->update($created->id, new UpdateProcurementDTO(
        status: 'ordered',
    ), $this->orgId);

    $this->service->update($created->id, new UpdateProcurementDTO(
        status: 'received',
    ), $this->orgId);

    expect(fn () => $this->service->update($created->id, new UpdateProcurementDTO(
        status: 'approved',
    ), $this->orgId))->toThrow(BusinessException::class);
});

it('finds procurement order by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-003', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->order_number)->toBe('PO-003');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-004', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates procurement orders scoped to organization', function (): void {
    $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-005', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));
    $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-006', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates procurement order from DTO', function (): void {
    $created = $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-007', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));

    $updated = $this->service->update($created->id, new UpdateProcurementDTO(
        status: 'approved',
        notes: 'Updated notes',
        totalAmount: '500000',
    ), $this->orgId);

    expect($updated->status)->toBe('approved');
    expect($updated->notes)->toBe('Updated notes');
    expect($updated->total_amount)->toBe('500000.00');
});

it('soft-deletes procurement order', function (): void {
    $created = $this->service->create(new CreateProcurementDTO(
        orderNumber: 'PO-008', orderDate: '2026-08-17',
        organizationId: $this->orgId, supplierId: null, branchId: null,
        expectedDate: null, totalAmount: null, items: null, notes: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Procurement::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});