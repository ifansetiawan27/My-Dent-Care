<?php

declare(strict_types=1);

use App\Domains\Employee\Models\Employee;
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
        'id' => $this->orgId, 'company_code' => 'ORG-EMP-01', 'company_name' => 'Employee Test Org',
        'email' => 'emp@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-EMP-01',
        'branch_name' => 'Employee Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Employee', 'email' => 'test-emp@test.com', 'username' => 'testemp',
        'employee_code' => 'EMP-TEST-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates employee and returns 201', function (): void {
    $response = $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-001',
        'full_name'        => 'API Employee',
        'organization_id'  => $this->orgId,
        'branch_id'        => $this->branchId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
        'position'         => 'Dentist',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.employee_code', 'EMP-API-001');
});

it('validates required fields on create', function (): void {
    $response = $this->postJson('/api/v1/employees', []);
    $response->assertStatus(422);
});

it('rejects duplicate employee_code on create', function (): void {
    $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-002',
        'full_name'        => 'First',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ])->assertStatus(201);

    $response = $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-002',
        'full_name'        => 'Second',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ]);

    $response->assertStatus(422);
});

it('lists employees', function (): void {
    $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-003',
        'full_name'        => 'List One',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ])->assertStatus(201);

    $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-004',
        'full_name'        => 'List Two',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ])->assertStatus(201);

    $response = $this->getJson('/api/v1/employees');
    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
});

it('shows employee by id', function (): void {
    $create = $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-005',
        'full_name'        => 'Show Me',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->getJson("/api/v1/employees/{$id}");
    $response->assertStatus(200);
    $response->assertJsonPath('data.employee_code', 'EMP-API-005');
});

it('returns 404 for nonexistent employee', function (): void {
    $response = $this->getJson('/api/v1/employees/' . (string) Str::orderedUuid());
    $response->assertStatus(404);
});

it('updates employee', function (): void {
    $create = $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-006',
        'full_name'        => 'Before Update',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->putJson("/api/v1/employees/{$id}", [
        'full_name' => 'After Update',
        'position'  => 'Manager',
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.full_name', 'After Update');
});

it('soft-deletes employee', function (): void {
    $create = $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-007',
        'full_name'        => 'Delete Me',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $response = $this->deleteJson("/api/v1/employees/{$id}");
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $create = $this->postJson('/api/v1/employees', [
        'employee_code'    => 'EMP-API-008',
        'full_name'        => 'Toggle Me',
        'organization_id'  => $this->orgId,
        'employment_status'=> 'active',
        'hire_date'        => '2026-01-01',
    ])->assertStatus(201);

    $id = $create->json('data.id');
    $this->patchJson("/api/v1/employees/{$id}/toggle-active")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', false);

    $this->patchJson("/api/v1/employees/{$id}/toggle-active")
        ->assertStatus(200)
        ->assertJsonPath('data.is_active', true);
});