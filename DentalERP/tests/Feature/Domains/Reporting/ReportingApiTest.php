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
        'id' => $this->orgId, 'company_code' => 'ORG-RPT-01', 'company_name' => 'Reporting Test Org',
        'email' => 'rpt@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-RPT-01',
        'branch_name' => 'Reporting Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Reporting', 'email' => 'test-rpt@test.com', 'username' => 'testrpt',
        'employee_code' => 'EMP-RPT-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates report and returns 201', function (): void {
        $response = $this->postJson('/api/v1/reports', [
            'report_type' => 'financial',
            'name'        => 'Monthly Revenue',
            'report_date' => '2026-08-17',
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'generated');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/reports', [])->assertStatus(422);
    });

    it('lists reports', function (): void {
        $this->postJson('/api/v1/reports', [
            'report_type' => 'financial', 'name' => 'Report A', 'report_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->postJson('/api/v1/reports', [
            'report_type' => 'inventory', 'name' => 'Report B', 'report_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/reports')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows report by id', function (): void {
        $c = $this->postJson('/api/v1/reports', [
            'report_type' => 'financial', 'name' => 'Show Me', 'report_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->getJson('/api/v1/reports/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Show Me');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/reports/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates report', function (): void {
        $c = $this->postJson('/api/v1/reports', [
            'report_type' => 'financial', 'name' => 'Before Update', 'report_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->putJson('/api/v1/reports/' . $c->json('data.id'), [
            'name'   => 'After Update',
            'status' => 'archived',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'After Update')
            ->assertJsonPath('data.status', 'archived');
    });

    it('soft-deletes report', function (): void {
        $c = $this->postJson('/api/v1/reports', [
            'report_type' => 'financial', 'name' => 'Delete Me', 'report_date' => '2026-08-17',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/reports/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/reports', [
        'report_type' => 'financial',
        'name'        => 'No Auth',
        'report_date' => '2026-08-17',
    ])->assertStatus(401);
});