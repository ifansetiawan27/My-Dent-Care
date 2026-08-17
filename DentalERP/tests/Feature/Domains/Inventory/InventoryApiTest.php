<?php

declare(strict_types=1);

use App\Domains\Inventory\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->orgId    = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $this->userId   = (string) Str::orderedUuid();
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
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Inventory', 'email' => 'test-inv@test.com', 'username' => 'testinv',
        'employee_code' => 'EMP-INV-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates inventory item and returns 201', function (): void {
    $response = $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-001',
        'name'      => 'Dental Chair',
        'unit'      => 'unit',
        'branch_id' => $this->branchId,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.item_code', 'ITM-API-001');
    $response->assertJsonPath('data.is_active', true);
});

it('validates required fields on create', function (): void {
    $response = $this->postJson('/api/v1/inventory-items', []);
    $response->assertStatus(422);
});

it('rejects duplicate item_code on create', function (): void {
    $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-002',
        'name'      => 'First',
        'unit'      => 'unit',
    ])->assertStatus(201);

    $response = $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-002',
        'name'      => 'Second',
        'unit'      => 'unit',
    ]);

    $response->assertStatus(422);
});

it('lists inventory items', function (): void {
    $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-003',
        'name'      => 'List One',
        'unit'      => 'unit',
    ])->assertStatus(201);

    $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-004',
        'name'      => 'List Two',
        'unit'      => 'unit',
    ])->assertStatus(201);

    $response = $this->getJson('/api/v1/inventory-items');
    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
});

it('shows inventory item by id', function (): void {
    $create = $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-005',
        'name'      => 'Show Me',
        'unit'      => 'unit',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->getJson("/api/v1/inventory-items/{$id}");
    $response->assertStatus(200);
    $response->assertJsonPath('data.item_code', 'ITM-API-005');
});

it('returns 404 for nonexistent inventory item', function (): void {
    $response = $this->getJson('/api/v1/inventory-items/' . (string) Str::orderedUuid());
    $response->assertStatus(404);
});

it('updates inventory item', function (): void {
    $create = $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-006',
        'name'      => 'Before Update',
        'unit'      => 'unit',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->putJson("/api/v1/inventory-items/{$id}", [
        'name'        => 'After Update',
        'description' => 'Updated desc',
        'quantity'    => '100',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.name', 'After Update');
    $response->assertJsonPath('data.quantity', '100.00');
});

it('soft-deletes inventory item', function (): void {
    $create = $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-007',
        'name'      => 'Delete Me',
        'unit'      => 'unit',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->deleteJson("/api/v1/inventory-items/{$id}");
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $create = $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-008',
        'name'      => 'Toggle Me',
        'unit'      => 'unit',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $this->patchJson("/api/v1/inventory-items/{$id}/toggle-active")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', false);

    $this->patchJson("/api/v1/inventory-items/{$id}/toggle-active")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', true);
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/inventory-items', [
        'item_code' => 'ITM-API-009',
        'name'      => 'No Auth',
        'unit'      => 'unit',
    ])->assertStatus(401);
});