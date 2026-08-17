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
        'id' => $this->orgId, 'company_code' => 'ORG-HR-01', 'company_name' => 'HR Test Org',
        'email' => 'hr@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-HR-01',
        'branch_name' => 'HR Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test HR', 'email' => 'test-hr@test.com', 'username' => 'testhr',
        'employee_code' => 'EMP-HR-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates HR record and returns 201', function (): void {
        $response = $this->postJson('/api/v1/hr-records', [
            'record_type'    => 'employment',
            'effective_date' => '2026-08-17',
        ]);
        $response->assertStatus(201);
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/hr-records', [])->assertStatus(422);
    });

    it('rejects invalid status transition', function (): void {
        $c = $this->postJson('/api/v1/hr-records', [
            'record_type' => 'employment', 'effective_date' => '2026-08-17',
        ])->assertStatus(201);

        $this->putJson('/api/v1/hr-records/' . $c->json('data.id'), ['status' => 'archived'])->assertStatus(200);

        $this->putJson('/api/v1/hr-records/' . $c->json('data.id'), ['status' => 'active'])
            ->assertStatus(422);
    });

    it('lists HR records', function (): void {
        $this->postJson('/api/v1/hr-records', [
            'record_type' => 'employment', 'effective_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->postJson('/api/v1/hr-records', [
            'record_type' => 'promotion', 'effective_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/hr-records')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows HR record by id', function (): void {
        $c = $this->postJson('/api/v1/hr-records', [
            'record_type' => 'employment', 'effective_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/hr-records/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.record_type', 'employment');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/hr-records/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates HR record', function (): void {
        $c = $this->postJson('/api/v1/hr-records', [
            'record_type' => 'employment', 'effective_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->putJson('/api/v1/hr-records/' . $c->json('data.id'), [
            'record_type' => 'promotion',
            'status'      => 'inactive',
            'notes'       => 'Updated notes',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.record_type', 'promotion')
            ->assertJsonPath('data.status', 'inactive');
    });

    it('soft-deletes HR record', function (): void {
        $c = $this->postJson('/api/v1/hr-records', [
            'record_type' => 'employment', 'effective_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/hr-records/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/hr-records', [
        'record_type'    => 'employment',
        'effective_date' => '2026-08-17',
    ])->assertStatus(401);
});