<?php

declare(strict_types=1);

use App\Domains\Pharmacy\Models\Pharmacy;
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
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Pharmacy', 'email' => 'test-pharm@test.com', 'username' => 'testpharm',
        'employee_code' => 'EMP-PHARM-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates pharmacy item and returns 201', function (): void {
    $response = $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-001',
        'name'      => 'Amoxicillin',
        'branch_id' => $this->branchId,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.drug_code', 'DRG-API-001');
    $response->assertJsonPath('data.is_active', true);
});

it('validates required fields on create', function (): void {
    $response = $this->postJson('/api/v1/pharmacy-items', []);
    $response->assertStatus(422);
});

it('rejects duplicate drug_code on create', function (): void {
    $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-002',
        'name'      => 'First',
    ])->assertStatus(201);

    $response = $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-002',
        'name'      => 'Second',
    ]);

    $response->assertStatus(422);
});

it('lists pharmacy items', function (): void {
    $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-003',
        'name'      => 'List One',
    ])->assertStatus(201);

    $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-004',
        'name'      => 'List Two',
    ])->assertStatus(201);

    $response = $this->getJson('/api/v1/pharmacy-items');
    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
});

it('shows pharmacy item by id', function (): void {
    $create = $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-005',
        'name'      => 'Show Me',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->getJson("/api/v1/pharmacy-items/{$id}");
    $response->assertStatus(200);
    $response->assertJsonPath('data.drug_code', 'DRG-API-005');
});

it('returns 404 for nonexistent pharmacy item', function (): void {
    $response = $this->getJson('/api/v1/pharmacy-items/' . (string) Str::orderedUuid());
    $response->assertStatus(404);
});

it('updates pharmacy item', function (): void {
    $create = $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-006',
        'name'      => 'Before Update',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->putJson("/api/v1/pharmacy-items/{$id}", [
        'name'         => 'After Update',
        'category'     => 'Antibiotic',
        'quantity'     => '100',
        'batch_number' => 'BATCH-API-001',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.name', 'After Update');
    $response->assertJsonPath('data.quantity', '100.00');
    $response->assertJsonPath('data.batch_number', 'BATCH-API-001');
});

it('soft-deletes pharmacy item', function (): void {
    $create = $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-007',
        'name'      => 'Delete Me',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->deleteJson("/api/v1/pharmacy-items/{$id}");
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $create = $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-008',
        'name'      => 'Toggle Me',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $this->patchJson("/api/v1/pharmacy-items/{$id}/toggle-active")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', false);

    $this->patchJson("/api/v1/pharmacy-items/{$id}/toggle-active")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', true);
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/pharmacy-items', [
        'drug_code' => 'DRG-API-009',
        'name'      => 'No Auth',
    ])->assertStatus(401);
});