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
        'id' => $this->orgId, 'company_code' => 'ORG-EMR-01', 'company_name' => 'EMR Test Org',
        'email' => 'emr@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-EMR-01',
        'branch_name' => 'EMR Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test EMR', 'email' => 'test-emr@test.com', 'username' => 'testemr',
        'employee_code' => 'EMP-EMR-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-EMR-01',
        'full_name' => 'EMR Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates emr and returns 201', function (): void {
    $response = $this->postJson('/api/v1/emrs', [
        'organization_id' => $this->orgId,
        'patient_id'      => $this->patientId,
        'chief_complaint' => 'Toothache',
        'status'          => 'open',
    ]);
    $response->assertStatus(201);
    $response->assertJsonPath('status', 'open');
});

it('validates required fields', function (): void {
    $this->postJson('/api/v1/emrs', [])->assertStatus(422);
});

it('creates duplicate emrs', function (): void {
    $payload = [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
    ];
    $this->postJson('/api/v1/emrs', $payload)->assertStatus(201);
    $this->postJson('/api/v1/emrs', $payload)->assertStatus(201);
});

it('lists emrs', function (): void {
    $this->postJson('/api/v1/emrs', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
    ])->assertStatus(201);
    $this->postJson('/api/v1/emrs', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
    ])->assertStatus(201);
    $this->getJson('/api/v1/emrs')->assertStatus(200)->assertJsonCount(2, 'data');
});

it('shows emr by id', function (): void {
    $c = $this->postJson('/api/v1/emrs', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
    ])->assertStatus(201);
    $this->getJson('/api/v1/emrs/' . $c->json('id'))
        ->assertStatus(200);
});

it('returns 404 for nonexistent', function (): void {
    $this->getJson('/api/v1/emrs/' . (string) Str::orderedUuid())->assertStatus(404);
});

it('updates emr', function (): void {
    $c = $this->postJson('/api/v1/emrs', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
    ])->assertStatus(201);
    $this->putJson('/api/v1/emrs/' . $c->json('id'), ['diagnosis' => 'Updated'])
        ->assertStatus(200)
        ->assertJsonPath('diagnosis', 'Updated');
});

it('soft-deletes emr', function (): void {
    $c = $this->postJson('/api/v1/emrs', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
    ])->assertStatus(201);
    $this->deleteJson('/api/v1/emrs/' . $c->json('id'))
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $c = $this->postJson('/api/v1/emrs', [
        'organization_id' => $this->orgId, 'patient_id' => $this->patientId,
        'status' => 'open',
    ])->assertStatus(201);
    $this->patchJson('/api/v1/emrs/' . $c->json('id') . '/toggle-active')
        ->assertStatus(200)
        ->assertJsonPath('status', 'completed');
});