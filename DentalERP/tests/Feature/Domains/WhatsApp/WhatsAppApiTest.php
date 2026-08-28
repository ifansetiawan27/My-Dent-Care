<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::fake([
        '*/api/status' => Http::response(['status' => 'disconnected']),
        '*/api/send' => Http::response(['status' => 'success']),
        '*/api/qr' => Http::response(['status' => 'qr_ready', 'qr_code' => 'base64qr']),
        '*/api/logout' => Http::response(['status' => 'disconnected']),
    ]);

    $this->orgId = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-WA-01', 'company_name' => 'WhatsApp Test Org',
        'email' => 'wa@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-WA-01',
        'branch_name' => 'WhatsApp Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'sanctum']);
});

function createWaUser(string $orgId, string $branchId, string $email, string $role): \App\Domains\User\Models\User
{
    $userId = (string) Str::orderedUuid();

    DB::table('users')->insert([
        'id' => $userId, 'organization_id' => $orgId, 'branch_id' => $branchId,
        'name' => 'WA Tester', 'email' => $email, 'username' => Str::random(8),
        'employee_code' => 'EMP-WA-' . Str::upper(Str::random(4)),
        'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $user = \App\Domains\User\Models\User::find($userId);
    $user->assignRole($role);

    return $user;
}

it('rejects unauthenticated whatsapp requests', function (): void {
    $this->getJson('/api/v1/whatsapp/status')->assertStatus(401);
    $this->postJson('/api/v1/whatsapp/test-send', [
        'phone' => '081234567890', 'message' => 'hi',
    ])->assertStatus(401);
});

it('allows any authenticated user to read whatsapp status', function (): void {
    $user = createWaUser($this->orgId, $this->branchId, 'receptionist-wa@test.com', 'receptionist');

    $this->actingAs($user)
        ->getJson('/api/v1/whatsapp/status')
        ->assertStatus(200)
        ->assertJsonPath('status', 'disconnected');
});

it('blocks non-admin users from sending whatsapp messages', function (): void {
    $user = createWaUser($this->orgId, $this->branchId, 'receptionist-wa2@test.com', 'receptionist');

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/test-send', ['phone' => '081234567890', 'message' => 'hi'])
        ->assertStatus(403);

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/test-reminder')
        ->assertStatus(403);

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/qr')
        ->assertStatus(403);

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/logout')
        ->assertStatus(403);
});

it('allows admin users to send whatsapp messages', function (): void {
    $user = createWaUser($this->orgId, $this->branchId, 'admin-wa@test.com', 'admin');

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/test-send', ['phone' => '081234567890', 'message' => 'hi'])
        ->assertStatus(200)
        ->assertJsonPath('status', 'success');
});

it('allows super admin users to generate qr and test reminders', function (): void {
    $user = createWaUser($this->orgId, $this->branchId, 'super-wa@test.com', 'super_admin');

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/qr')
        ->assertStatus(200)
        ->assertJsonPath('qr_code', 'base64qr');

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/test-reminder')
        ->assertStatus(200)
        ->assertJsonPath('status', 'success');
});

it('validates test-send payload', function (): void {
    $user = createWaUser($this->orgId, $this->branchId, 'admin-wa2@test.com', 'admin');

    $this->actingAs($user)
        ->postJson('/api/v1/whatsapp/test-send', [])
        ->assertStatus(422);
});
