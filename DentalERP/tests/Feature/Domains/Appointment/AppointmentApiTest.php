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
        'id' => $this->orgId, 'company_code' => 'ORG-APT-01', 'company_name' => 'Appointment Test Org',
        'email' => 'apt@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-APT-01',
        'branch_name' => 'Appointment Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Appointment', 'email' => 'test-apt@test.com', 'username' => 'testapt',
        'employee_code' => 'EMP-APT-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates appointment and returns 201', function (): void {
    $response = $this->postJson('/api/v1/appointments', [
        'organization_id' => $this->orgId,
        'branch_id'       => $this->branchId,
        'scheduled_at'    => now()->addDay()->toDateTimeString(),
        'status'          => 'scheduled',
        'type'            => 'checkup',
    ]);
    $response->assertStatus(201);
    $response->assertJsonPath('status', 'scheduled');
});

it('validates required fields', function (): void {
    $this->postJson('/api/v1/appointments', [])->assertStatus(422);
});

it('creates duplicate appointments', function (): void {
    $payload = [
        'organization_id' => $this->orgId,
        'scheduled_at'    => now()->addDay()->toDateTimeString(),
    ];
    $this->postJson('/api/v1/appointments', $payload)->assertStatus(201);
    $this->postJson('/api/v1/appointments', $payload)->assertStatus(201);
});

it('lists appointments', function (): void {
    $this->postJson('/api/v1/appointments', [
        'organization_id' => $this->orgId, 'scheduled_at' => now()->addDay()->toDateTimeString(),
    ])->assertStatus(201);
    $this->postJson('/api/v1/appointments', [
        'organization_id' => $this->orgId, 'scheduled_at' => now()->addDays(2)->toDateTimeString(),
    ])->assertStatus(201);
    $this->getJson('/api/v1/appointments')->assertStatus(200)->assertJsonCount(2, 'data');
});

it('shows appointment by id', function (): void {
    $c = $this->postJson('/api/v1/appointments', [
        'organization_id' => $this->orgId, 'scheduled_at' => now()->addDay()->toDateTimeString(),
    ])->assertStatus(201);
    $this->getJson('/api/v1/appointments/' . $c->json('id'))
        ->assertStatus(200);
});

it('returns 404 for nonexistent', function (): void {
    $this->getJson('/api/v1/appointments/' . (string) Str::orderedUuid())->assertStatus(404);
});

it('updates appointment', function (): void {
    $c = $this->postJson('/api/v1/appointments', [
        'organization_id' => $this->orgId, 'scheduled_at' => now()->addDay()->toDateTimeString(),
    ])->assertStatus(201);
    $this->putJson('/api/v1/appointments/' . $c->json('id'), ['status' => 'confirmed'])
        ->assertStatus(200)
        ->assertJsonPath('status', 'confirmed');
});

it('soft-deletes appointment', function (): void {
    $c = $this->postJson('/api/v1/appointments', [
        'organization_id' => $this->orgId, 'scheduled_at' => now()->addDay()->toDateTimeString(),
    ])->assertStatus(201);
    $this->deleteJson('/api/v1/appointments/' . $c->json('id'))
        ->assertStatus(200)
        ->assertJsonPath('success', true);
});

it('toggles active status', function (): void {
    $c = $this->postJson('/api/v1/appointments', [
        'organization_id' => $this->orgId, 'scheduled_at' => now()->addDay()->toDateTimeString(),
        'status' => 'scheduled',
    ])->assertStatus(201);
    $this->patchJson('/api/v1/appointments/' . $c->json('id') . '/toggle-active')
        ->assertStatus(200)
        ->assertJsonPath('status', 'cancelled');
});