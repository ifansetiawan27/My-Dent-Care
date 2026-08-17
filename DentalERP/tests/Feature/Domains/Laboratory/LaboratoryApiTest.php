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
        'id' => $this->orgId, 'company_code' => 'ORG-LAB-01', 'company_name' => 'Lab Test Org',
        'email' => 'lab@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-LAB-01',
        'branch_name' => 'Lab Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Lab', 'email' => 'test-lab@test.com', 'username' => 'testlab',
        'employee_code' => 'EMP-LAB-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-LAB-01',
        'full_name' => 'Lab Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates lab order and returns 201', function (): void {
        $response = $this->postJson('/api/v1/lab-orders', [
            'patient_id'   => $this->patientId,
            'order_number' => 'LAB-API-001',
            'ordered_at'   => '2026-08-17',
            'doctor_id'    => null,
            'category_id'  => null,
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.order_number', 'LAB-API-001');
        $response->assertJsonPath('data.status', 'pending');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/lab-orders', [])->assertStatus(422);
    });

    it('rejects duplicate order_number', function (): void {
        $this->postJson('/api/v1/lab-orders', [
            'patient_id'   => $this->patientId,
            'order_number' => 'LAB-API-002',
            'ordered_at'   => '2026-08-17',
        ])->assertStatus(201);

        $this->postJson('/api/v1/lab-orders', [
            'patient_id'   => $this->patientId,
            'order_number' => 'LAB-API-002',
            'ordered_at'   => '2026-08-17',
        ])->assertStatus(422);
    });

    it('rejects invalid status transition', function (): void {
        $c = $this->postJson('/api/v1/lab-orders', [
            'patient_id'   => $this->patientId,
            'order_number' => 'LAB-API-003',
            'ordered_at'   => '2026-08-17',
        ])->assertStatus(201);

        $this->putJson('/api/v1/lab-orders/' . $c->json('data.id'), ['status' => 'completed'])
            ->assertStatus(422);
    });

    it('lists lab orders', function (): void {
        $this->postJson('/api/v1/lab-orders', [
            'patient_id' => $this->patientId, 'order_number' => 'LAB-API-004', 'ordered_at' => '2026-08-17',
        ])->assertStatus(201);
        $this->postJson('/api/v1/lab-orders', [
            'patient_id' => $this->patientId, 'order_number' => 'LAB-API-005', 'ordered_at' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/lab-orders')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows lab order by id', function (): void {
        $c = $this->postJson('/api/v1/lab-orders', [
            'patient_id' => $this->patientId, 'order_number' => 'LAB-API-006', 'ordered_at' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/lab-orders/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.order_number', 'LAB-API-006');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/lab-orders/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates lab order', function (): void {
        $c = $this->postJson('/api/v1/lab-orders', [
            'patient_id' => $this->patientId, 'order_number' => 'LAB-API-007', 'ordered_at' => '2026-08-17',
        ])->assertStatus(201);
        $this->putJson('/api/v1/lab-orders/' . $c->json('data.id'), [
            'description' => 'Updated description',
            'notes'       => 'Updated notes',
            'status'      => 'in_progress',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.description', 'Updated description')
            ->assertJsonPath('data.notes', 'Updated notes')
            ->assertJsonPath('data.status', 'in_progress');
    });

    it('soft-deletes lab order', function (): void {
        $c = $this->postJson('/api/v1/lab-orders', [
            'patient_id' => $this->patientId, 'order_number' => 'LAB-API-008', 'ordered_at' => '2026-08-17',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/lab-orders/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/lab-orders', [
        'patient_id'   => $this->patientId,
        'order_number' => 'LAB-API-009',
        'ordered_at'   => '2026-08-17',
    ])->assertStatus(401);
});