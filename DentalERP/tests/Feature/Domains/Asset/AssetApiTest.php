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
        'id' => $this->orgId, 'company_code' => 'ORG-ASST-01', 'company_name' => 'Asset Test Org',
        'email' => 'asset@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-ASST-01',
        'branch_name' => 'Asset Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Asset', 'email' => 'test-asset@test.com', 'username' => 'testasset',
        'employee_code' => 'EMP-ASST-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates asset and returns 201', function (): void {
        $response = $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-001',
            'name'       => 'Dental Chair',
            'branch_id'  => $this->branchId,
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.asset_code', 'AST-API-001');
        $response->assertJsonPath('data.status', 'active');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/assets', [])->assertStatus(422);
    });

    it('rejects duplicate asset_code', function (): void {
        $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-002',
            'name'       => 'First',
        ])->assertStatus(201);

        $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-002',
            'name'       => 'Second',
        ])->assertStatus(422);
    });

    it('rejects invalid status transition', function (): void {
        $c = $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-003',
            'name'       => 'Test Asset',
        ])->assertStatus(201);

        $this->putJson('/api/v1/assets/' . $c->json('data.id'), ['status' => 'disposed'])->assertStatus(200);

        $this->putJson('/api/v1/assets/' . $c->json('data.id'), ['status' => 'active'])
            ->assertStatus(422);
    });

    it('lists assets', function (): void {
        $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-004', 'name' => 'Asset One',
        ])->assertStatus(201);
        $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-005', 'name' => 'Asset Two',
        ])->assertStatus(201);
        $this->getJson('/api/v1/assets')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows asset by id', function (): void {
        $c = $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-006', 'name' => 'Show Me',
        ])->assertStatus(201);
        $this->getJson('/api/v1/assets/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.asset_code', 'AST-API-006');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/assets/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates asset', function (): void {
        $c = $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-007', 'name' => 'Before Update',
        ])->assertStatus(201);
        $this->putJson('/api/v1/assets/' . $c->json('data.id'), [
            'name'        => 'After Update',
            'status'      => 'maintenance',
            'description' => 'Updated description',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'After Update')
            ->assertJsonPath('data.status', 'maintenance');
    });

    it('soft-deletes asset', function (): void {
        $c = $this->postJson('/api/v1/assets', [
            'asset_code' => 'AST-API-008', 'name' => 'Delete Me',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/assets/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/assets', [
        'asset_code' => 'AST-API-009',
        'name'       => 'No Auth',
    ])->assertStatus(401);
});