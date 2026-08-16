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
        'id' => $this->orgId, 'company_code' => 'ORG-DOC-01', 'company_name' => 'Doctor Test Org',
        'email' => 'doc@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-DOC-01',
        'branch_name' => 'Doctor Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Doctor', 'email' => 'test-doc@test.com', 'username' => 'testdoc',
        'employee_code' => 'EMP-DOC-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates doctor and returns 201', function (): void {
    $response = $this->postJson('/api/v1/doctors', [
        'doctor_code'    => 'DOC-API-001',
        'full_name'      => 'Dr. API',
        'organization_id' => $this->orgId,
        'branch_id'      => $this->branchId,
    ]);
    $response->assertStatus(201);
    $response->assertJsonPath('data.doctor_code', 'DOC-API-001');
});

it('validates required fields', function (): void {
    $this->postJson('/api/v1/doctors', [])->assertStatus(422);
});

it('rejects duplicate doctor_code', function (): void {
    $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-002', 'full_name' => 'First', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-002', 'full_name' => 'Second', 'organization_id' => $this->orgId,
    ])->assertStatus(422);
});

it('lists doctors', function (): void {
    $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-003', 'full_name' => 'List One', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-004', 'full_name' => 'List Two', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->getJson('/api/v1/doctors')->assertStatus(200)->assertJsonCount(2, 'data');
});

it('shows doctor by id', function (): void {
    $c = $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-005', 'full_name' => 'Show Me', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->getJson('/api/v1/doctors/' . $c->json('data.id'))
        ->assertStatus(200)
        ->assertJsonPath('data.doctor_code', 'DOC-API-005');
});

it('returns 404 for nonexistent', function (): void {
    $this->getJson('/api/v1/doctors/' . (string) Str::orderedUuid())->assertStatus(404);
});

it('updates doctor', function (): void {
    $c = $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-006', 'full_name' => 'Before', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->putJson('/api/v1/doctors/' . $c->json('data.id'), ['full_name' => 'After'])
        ->assertStatus(200)
        ->assertJsonPath('data.full_name', 'After');
});

it('soft-deletes doctor', function (): void {
    $c = $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-007', 'full_name' => 'Delete Me', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->deleteJson('/api/v1/doctors/' . $c->json('data.id'))
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $c = $this->postJson('/api/v1/doctors', [
        'doctor_code' => 'DOC-API-008', 'full_name' => 'Toggle', 'organization_id' => $this->orgId,
    ])->assertStatus(201);
    $this->patchJson('/api/v1/doctors/' . $c->json('data.id') . '/toggle-active')
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', false);
});