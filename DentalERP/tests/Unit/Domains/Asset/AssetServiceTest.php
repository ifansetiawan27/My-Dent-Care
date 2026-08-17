<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Asset\DTO\CreateAssetDTO;
use App\Domains\Asset\DTO\UpdateAssetDTO;
use App\Domains\Asset\Interfaces\AssetServiceInterface;
use App\Domains\Asset\Models\Asset;
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
        'id' => $this->orgId, 'company_code' => 'ORG-ASST-01', 'company_name' => 'Asset Test Org',
        'email' => 'asset@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-ASST-01',
        'branch_name' => 'Asset Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(AssetServiceInterface::class);
});

it('creates asset from DTO and returns Asset model', function (): void {
    $dto = new CreateAssetDTO(
        assetCode: 'AST-001',
        name: 'Dental Chair',
        organizationId: $this->orgId,
        branchId: $this->branchId,
        categoryId: null,
        description: null,
        purchaseDate: null,
        purchasePrice: null,
        warrantyExpiry: null,
        notes: null,
    );

    $asset = $this->service->create($dto);

    expect($asset)->toBeInstanceOf(Asset::class);
    expect($asset->asset_code)->toBe('AST-001');
    expect($asset->name)->toBe('Dental Chair');
    expect($asset->organization_id)->toBe($this->orgId);
    expect($asset->status)->toBe('active');
});

it('throws BusinessException when asset_code already exists', function (): void {
    $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-001', name: 'First Asset',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));

    expect(fn () => $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-001', name: 'Second Asset',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    )))->toThrow(BusinessException::class, 'Asset code already exists.');
});

it('throws BusinessException when status transition is invalid (disposed → active)', function (): void {
    $created = $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-002', name: 'Disposable Asset',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));

    $this->service->update($created->id, new UpdateAssetDTO(
        status: 'disposed',
    ), $this->orgId);

    expect(fn () => $this->service->update($created->id, new UpdateAssetDTO(
        status: 'active',
    ), $this->orgId))->toThrow(BusinessException::class);
});

it('finds asset by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-003', name: 'Asset Three',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->asset_code)->toBe('AST-003');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-004', name: 'Asset Four',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates assets scoped to organization', function (): void {
    $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-005', name: 'Asset Five',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));
    $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-006', name: 'Asset Six',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates asset from DTO', function (): void {
    $created = $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-007', name: 'Original',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));

    $updated = $this->service->update($created->id, new UpdateAssetDTO(
        name: 'Updated Name',
        status: 'maintenance',
        notes: 'Scheduled maintenance',
        purchasePrice: '15000000',
    ), $this->orgId);

    expect($updated->name)->toBe('Updated Name');
    expect($updated->status)->toBe('maintenance');
    expect($updated->notes)->toBe('Scheduled maintenance');
    expect($updated->purchase_price)->toBe('15000000.00');
});

it('soft-deletes asset', function (): void {
    $created = $this->service->create(new CreateAssetDTO(
        assetCode: 'AST-008', name: 'Deletable',
        organizationId: $this->orgId, branchId: null, categoryId: null,
        description: null, purchaseDate: null, purchasePrice: null,
        warrantyExpiry: null, notes: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Asset::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});