<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Organization\Models\Organization;
use App\Domains\Branch\Models\Branch;
use App\Domains\User\Models\User;
use App\Domains\Branch\Factories\BranchFactory;
use App\Domains\User\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Critical Path Tests
 *
 * These tests verify the most important user journeys:
 * 1. Auth flow (login, logout, profile)
 * 2. Multi-tenant isolation
 * 3. API route prefix (no double /api/api/v1/)
 * 4. CORS configuration
 * 5. API response format consistency
 * 6. Health check endpoint
 *
 * Run with: docker compose -f docker/compose.yaml exec app php artisan test
 */
class CriticalPathTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a valid login payload matching the current LoginRequest contract
     * (identifier + organization/branch scoping + device tracking).
     */
    private function loginPayload(User $user, string $identifier): array
    {
        return [
            'identifier'      => $identifier,
            'password'        => 'password',
            'organization_id' => $user->organization_id,
            'branch_id'       => $user->branch_id,
            'device_uuid'     => 'test-device-uuid',
            'device_name'     => 'test-device',
            'device_type'     => 'web',
            'platform'        => 'web',
        ];
    }

    // -------------------------------------------------------------------------
    // 1. Health Check
    // -------------------------------------------------------------------------

    public function test_health_check_returns_ok(): void
    {
        $response = $this->get('/up');
        $response->assertOk();
    }

    // -------------------------------------------------------------------------
    // 2. Authentication Flow
    // -------------------------------------------------------------------------

    public function test_user_can_login_and_get_profile(): void
    {
        $user = User::factory()
            ->withEmail('test@dentcare.com')
            ->withPassword('password')
            ->create();

        // Login
        $loginResponse = $this->postJson('/api/v1/auth/login', $this->loginPayload($user, 'test@dentcare.com'));

        $loginResponse->assertOk();
        $loginResponse->assertJsonStructure([
            'success',
            'data' => [
                'access_token',
                'user' => ['id', 'name', 'email'],
            ],
        ]);
        $loginResponse->assertJsonPath('success', true);

        $token = $loginResponse->json('data.access_token');

        // Get profile
        $profileResponse = $this->withToken($token)->getJson('/api/v1/auth/profile');
        $profileResponse->assertOk();
        $profileResponse->assertJsonPath('data.email', 'test@dentcare.com');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()
            ->withEmail('wrong@dentcare.com')
            ->withPassword('correct-password')
            ->create();

        $payload = $this->loginPayload($user, 'wrong@dentcare.com');
        $payload['password'] = 'wrong-password';

        $response = $this->postJson('/api/v1/auth/login', $payload);

        $response->assertUnauthorized();
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()
            ->withEmail('logout@dentcare.com')
            ->withPassword('password')
            ->create();

        $loginResponse = $this->postJson('/api/v1/auth/login', $this->loginPayload($user, 'logout@dentcare.com'));

        $token = $loginResponse->json('data.access_token');

        $logoutResponse = $this->withToken($token)->postJson('/api/v1/auth/logout');
        $logoutResponse->assertOk();

        // Reset cached guards so the next sub-request re-resolves the token
        // (in production every HTTP request starts with a fresh guard).
        $this->app['auth']->forgetGuards();

        // Token should be invalid after logout
        $profileResponse = $this->withToken($token)->getJson('/api/v1/auth/profile');
        $profileResponse->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // 3. API Route Prefix (no double /api/api/v1/)
    // -------------------------------------------------------------------------

    public function test_api_routes_use_single_api_prefix(): void
    {
        // Routes should be at /api/v1/* not /api/api/v1/*
        // Test that /api/v1/auth/login exists (not /api/api/v1/auth/login)
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@dentcare.com',
            'password' => 'test',
            'device_name' => 'test',
        ]);

        // Should reach the controller (returns 401 for bad creds, not 404)
        $this->assertTrue(
            in_array($response->status(), [401, 422]),
            'Route /api/v1/auth/login should exist (status: ' . $response->status() . ')'
        );

        // Double prefix should NOT exist
        $doublePrefixResponse = $this->postJson('/api/api/v1/auth/login', [
            'email' => 'test@dentcare.com',
            'password' => 'test',
            'device_name' => 'test',
        ]);

        // If this returns 404, double prefix is NOT registered (which is correct)
        $this->assertTrue(
            $doublePrefixResponse->status() === 404,
            'Route /api/api/v1/auth/login should NOT exist (double prefix bug)'
        );
    }

    // -------------------------------------------------------------------------
    // 4. Multi-Tenant Isolation
    // -------------------------------------------------------------------------

    public function test_user_cannot_access_another_organizations_data(): void
    {
        // Create two separate organizations with users
        $user1 = User::factory()
            ->withEmail('user1@org-a.com')
            ->withPassword('password')
            ->create();

        $user2 = User::factory()
            ->withEmail('user2@org-b.com')
            ->withPassword('password')
            ->create();

        // Authenticate as user1
        $loginResponse = $this->postJson('/api/v1/auth/login', $this->loginPayload($user1, 'user1@org-a.com'));
        $token = $loginResponse->json('data.access_token');

        // user1's profile should show org1 data, not org2
        $profileResponse = $this->withToken($token)->getJson('/api/v1/auth/profile');
        $profileResponse->assertOk();
        // The response should not contain user2's data
        $profileResponse->assertJsonMissing(['data' => ['email' => 'user2@org-b.com']]);
    }

    // -------------------------------------------------------------------------
    // 5. CORS Configuration
    // -------------------------------------------------------------------------

    public function test_cors_allows_configured_origins(): void
    {
        config(['cors.allowed_origins' => [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
        ]]);

        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
        ])->options('/api/v1/auth/login');

        // Should have CORS headers
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }

    // -------------------------------------------------------------------------
    // 6. API Response Format
    // -------------------------------------------------------------------------

    public function test_api_returns_consistent_json_format(): void
    {
        $user = User::factory()
            ->withEmail('format@dentcare.com')
            ->withPassword('password')
            ->create();

        $loginResponse = $this->postJson('/api/v1/auth/login', $this->loginPayload($user, 'format@dentcare.com'));

        // All API responses should have 'success' key
        $loginResponse->assertJsonStructure(['success', 'data', 'message']);
        $loginResponse->assertJsonPath('success', true);
    }

    public function test_api_error_has_consistent_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '', // invalid
            'password' => '',
            'device_name' => 'test',
        ]);

        $response->assertJsonStructure(['success', 'message']);
        $response->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // 7. Sanctum CSRF Cookie
    // -------------------------------------------------------------------------

    public function test_sanctum_csrf_cookie_endpoint(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');

        // Should return 204 (no content) with CSRF cookie set
        $response->assertNoContent();
    }
}
