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
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-ODO-01', 'company_name' => 'Odontogram Test Org',
        'email' => 'odo@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-ODO-01',
        'branch_name' => 'Odontogram Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Odontogram', 'email' => 'test-odo@test.com', 'username' => 'testodo',
        'employee_code' => 'EMP-ODO-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-ODO-01',
        'full_name' => 'Odontogram Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates odontogram and returns 201', function (): void {
    $response = $this->postJson('/api/v1/odontograms', [
        'organization_id' => $this->orgId,
        'patient_id'      => $this->patientId,
        'tooth_number'    => '11',
        'condition'       => 'healthy',
    ]);
    $response->assertStatus(201);
    $response->assertJsonPath('tooth_number', '11');
});

it('validates required fields', function (): void {
    $this->postJson('/api/v1/odontograms', [])->assertStatus(422);
});

it('creates duplicate odontograms', function (): void {
    $payload = [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'tooth_number' => '12',
    ];
    $this->postJson('/api/v1/odontograms', $payload)->assertStatus(201);
    $this->postJson('/api/v1/odontograms', $payload)->assertStatus(201);
});

it('lists odontograms', function (): void {
    $this->postJson('/api/v1/odontograms', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'tooth_number' => '13',
    ])->assertStatus(201);
    $this->postJson('/api/v1/odontograms', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'tooth_number' => '14',
    ])->assertStatus(201);
    $this->getJson('/api/v1/odontograms')->assertStatus(200)->assertJsonCount(2, 'data');
});

it('shows odontogram by id', function (): void {
    $c = $this->postJson('/api/v1/odontograms', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'tooth_number' => '15',
    ])->assertStatus(201);
    $this->getJson('/api/v1/odontograms/' . $c->json('id'))
        ->assertStatus(200);
});

it('returns 404 for nonexistent', function (): void {
    $this->getJson('/api/v1/odontograms/' . (string) Str::orderedUuid())->assertStatus(404);
});

it('updates odontogram', function (): void {
    $c = $this->postJson('/api/v1/odontograms', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'tooth_number' => '16',
    ])->assertStatus(201);
    $this->putJson('/api/v1/odontograms/' . $c->json('id'), ['condition' => 'caries'])
        ->assertStatus(200)
        ->assertJsonPath('condition', 'caries');
});

it('soft-deletes odontogram', function (): void {
    $c = $this->postJson('/api/v1/odontograms', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'tooth_number' => '17',
    ])->assertStatus(201);
    $this->deleteJson('/api/v1/odontograms/' . $c->json('id'))
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $c = $this->postJson('/api/v1/odontograms', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'tooth_number' => '18', 'condition' => 'healthy',
    ])->assertStatus(201);
    $this->patchJson('/api/v1/odontograms/' . $c->json('id') . '/toggle-active')
        ->assertStatus(200)
        ->assertJsonPath('condition', 'caries');
});