<?php

declare(strict_types=1);

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
        'id' => $this->orgId, 'company_code' => 'ORG-PAT-01', 'company_name' => 'Patient Test Org',
        'email' => 'pat@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-PAT-01',
        'branch_name' => 'Patient Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Patient', 'email' => 'test-pat@test.com', 'username' => 'testpat',
        'employee_code' => 'EMP-PAT-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates patient and returns 201', function (): void {
    $response = $this->postJson('/api/v1/patients', [
        'patient_code'   => 'PAT-API-001',
        'full_name'      => 'John API',
        'organization_id' => $this->orgId,
        'branch_id'      => $this->branchId,
    ]);
    $response->assertStatus(201);
    $response->assertJsonPath('data.patient_code', 'PAT-API-001');
});

it('validates required fields', function (): void {
    $this->postJson('/api/v1/patients', [])->assertStatus(422);
});

it('rejects duplicate patient_code', function (): void {
    $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-002', 'full_name' => 'First', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-002', 'full_name' => 'Second', 'organization_id' => $this->orgId,
    ])->assertStatus(422);
});

it('lists patients', function (): void {
    $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-003', 'full_name' => 'List One', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-004', 'full_name' => 'List Two', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->getJson('/api/v1/patients')->assertStatus(200)->assertJsonCount(2, 'data');
});

it('shows patient by id', function (): void {
    $c = $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-005', 'full_name' => 'Show Me', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->getJson('/api/v1/patients/' . $c->json('data.id'))
        ->assertStatus(200)
        ->assertJsonPath('data.patient_code', 'PAT-API-005');
});

it('returns 404 for nonexistent', function (): void {
    $this->getJson('/api/v1/patients/' . (string) Str::orderedUuid())->assertStatus(404);
});

it('updates patient', function (): void {
    $c = $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-006', 'full_name' => 'Before', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->putJson('/api/v1/patients/' . $c->json('data.id'), ['full_name' => 'After'])
        ->assertStatus(200)
        ->assertJsonPath('data.full_name', 'After');
});

it('soft-deletes patient', function (): void {
    $c = $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-007', 'full_name' => 'Delete Me', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->deleteJson('/api/v1/patients/' . $c->json('data.id'))
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $c = $this->postJson('/api/v1/patients', [
        'patient_code' => 'PAT-API-008', 'full_name' => 'Toggle', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->patchJson('/api/v1/patients/' . $c->json('data.id') . '/toggle-active')
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', false);
});