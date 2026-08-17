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
        'id' => $this->orgId, 'company_code' => 'ORG-DASH-01', 'company_name' => 'Dashboard Test Org',
        'email' => 'dash@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-DASH-01',
        'branch_name' => 'Dashboard Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Dashboard', 'email' => 'test-dash@test.com', 'username' => 'testdash',
        'employee_code' => 'EMP-DASH-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates dashboard and returns 201', function (): void {
        $response = $this->postJson('/api/v1/dashboards', [
            'name' => 'My Dashboard',
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'My Dashboard');
        $response->assertJsonPath('data.is_default', false);
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/dashboards', [])->assertStatus(422);
    });

    it('lists dashboards', function (): void {
        $this->postJson('/api/v1/dashboards', [
            'name' => 'Dashboard A',
        ])->assertStatus(201);
        $this->postJson('/api/v1/dashboards', [
            'name' => 'Dashboard B',
        ])->assertStatus(201);
        $this->getJson('/api/v1/dashboards')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows dashboard by id', function (): void {
        $c = $this->postJson('/api/v1/dashboards', [
            'name' => 'Show Me',
        ])->assertStatus(201);
        $this->getJson('/api/v1/dashboards/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Show Me');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/dashboards/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates dashboard', function (): void {
        $c = $this->postJson('/api/v1/dashboards', [
            'name' => 'Before Update',
        ])->assertStatus(201);
        $this->putJson('/api/v1/dashboards/' . $c->json('data.id'), [
            'name'       => 'After Update',
            'is_default' => true,
            'config'     => ['theme' => 'dark'],
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'After Update')
            ->assertJsonPath('data.is_default', true)
            ->assertJsonPath('data.config.theme', 'dark');
    });

    it('soft-deletes dashboard', function (): void {
        $c = $this->postJson('/api/v1/dashboards', [
            'name' => 'Delete Me',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/dashboards/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/dashboards', [
        'name' => 'No Auth',
    ])->assertStatus(401);
});