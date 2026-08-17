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
        'id' => $this->orgId, 'company_code' => 'ORG-TRT-01', 'company_name' => 'Treatment Test Org',
        'email' => 'trt@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-TRT-01',
        'branch_name' => 'Treatment Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Treatment', 'email' => 'test-trt@test.com', 'username' => 'testtrt',
        'employee_code' => 'EMP-TRT-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-TRT-01',
        'full_name' => 'Treatment Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates treatment and returns 201', function (): void {
        $response = $this->postJson('/api/v1/treatments', [
            'patient_id'     => $this->patientId,
            'treatment_type' => 'Cleaning',
            'doctor_id'      => null,
            'appointment_id' => null,
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.treatment_type', 'Cleaning');
        $response->assertJsonPath('data.status', 'planned');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/treatments', [])->assertStatus(422);
    });

    it('rejects invalid status transition', function (): void {
        $c = $this->postJson('/api/v1/treatments', [
            'patient_id'     => $this->patientId,
            'treatment_type' => 'Filling',
        ])->assertStatus(201);

        $this->putJson('/api/v1/treatments/' . $c->json('data.id'), ['status' => 'completed'])
            ->assertStatus(422);
    });

    it('lists treatments', function (): void {
        $this->postJson('/api/v1/treatments', [
            'patient_id' => $this->patientId, 'treatment_type' => 'List One',
        ])->assertStatus(201);
        $this->postJson('/api/v1/treatments', [
            'patient_id' => $this->patientId, 'treatment_type' => 'List Two',
        ])->assertStatus(201);
        $this->getJson('/api/v1/treatments')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows treatment by id', function (): void {
        $c = $this->postJson('/api/v1/treatments', [
            'patient_id' => $this->patientId, 'treatment_type' => 'Show Me',
        ])->assertStatus(201);
        $this->getJson('/api/v1/treatments/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.treatment_type', 'Show Me');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/treatments/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates treatment', function (): void {
        $c = $this->postJson('/api/v1/treatments', [
            'patient_id' => $this->patientId, 'treatment_type' => 'Before',
        ])->assertStatus(201);
        $this->putJson('/api/v1/treatments/' . $c->json('data.id'), ['treatment_type' => 'After'])
            ->assertStatus(200)
            ->assertJsonPath('data.treatment_type', 'After');
    });

    it('soft-deletes treatment', function (): void {
        $c = $this->postJson('/api/v1/treatments', [
            'patient_id' => $this->patientId, 'treatment_type' => 'Delete Me',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/treatments/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->postJson('/api/v1/treatments', [
        'patient_id'     => $this->patientId,
        'treatment_type' => 'Cleaning',
    ])->assertStatus(401);
});