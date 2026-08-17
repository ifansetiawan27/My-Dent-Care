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
        'id' => $this->orgId, 'company_code' => 'ORG-PROC-01', 'company_name' => 'Procurement Test Org',
        'email' => 'proc@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-PROC-01',
        'branch_name' => 'Procurement Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Procurement', 'email' => 'test-proc@test.com', 'username' => 'testproc',
        'employee_code' => 'EMP-PROC-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates procurement order and returns 201', function (): void {
        $response = $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-001',
            'order_date'   => '2026-08-17',
            'branch_id'    => $this->branchId,
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.order_number', 'PO-API-001');
        $response->assertJsonPath('data.status', 'pending');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/procurement-orders', [])->assertStatus(422);
    });

    it('rejects duplicate order_number', function (): void {
        $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-002',
            'order_date'   => '2026-08-17',
        ])->assertStatus(201);

        $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-002',
            'order_date'   => '2026-08-17',
        ])->assertStatus(422);
    });

    it('rejects invalid status transition', function (): void {
        $c = $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-003',
            'order_date'   => '2026-08-17',
        ])->assertStatus(201);

        $this->putJson('/api/v1/procurement-orders/' . $c->json('data.id'), ['status' => 'received'])
            ->assertStatus(422);
    });

    it('lists procurement orders', function (): void {
        $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-004', 'order_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-005', 'order_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/procurement-orders')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows procurement order by id', function (): void {
        $c = $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-006', 'order_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/procurement-orders/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.order_number', 'PO-API-006');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/procurement-orders/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates procurement order', function (): void {
        $c = $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-007', 'order_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->putJson('/api/v1/procurement-orders/' . $c->json('data.id'), [
            'status' => 'approved',
            'notes'  => 'Approved order',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.notes', 'Approved order');
    });

    it('soft-deletes procurement order', function (): void {
        $c = $this->postJson('/api/v1/procurement-orders', [
            'order_number' => 'PO-API-008', 'order_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/procurement-orders/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/procurement-orders', [
        'order_number' => 'PO-API-009',
        'order_date'   => '2026-08-17',
    ])->assertStatus(401);
});