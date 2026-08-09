<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Authentication;

use Tests\TestCase;

/**
 * AuthControllerTest
 *
 * Endpoint-level feature tests for all 12 Authentication operations.
 * Tests require a running application with database migrations,
 * Sanctum config, and Redis cache.
 *
 * STATUS: PLANNED — All tests skipped until integration environment is available.
 */
class AuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // -------------------------------------------------------------------------
    // POST /auth/login
    // -------------------------------------------------------------------------

    /** @return array<int, array<string, mixed>> */
    public function test_login_success(): void
    {
        $this->markTestSkipped('PLANNED — Requires running application with migrations, Sanctum, and Redis.');
    }

    public function test_login_invalid_credentials_returns_401(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_login_locked_account_returns_error(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // POST /auth/logout
    // -------------------------------------------------------------------------

    public function test_logout_revokes_current_session(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_logout_requires_authentication(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // POST /auth/logout-all
    // -------------------------------------------------------------------------

    public function test_logout_all_revokes_all_sessions(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // POST /auth/refresh
    // -------------------------------------------------------------------------

    public function test_refresh_rotates_token_pair(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_refresh_reuse_returns_409(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // POST /auth/forgot-password
    // -------------------------------------------------------------------------

    public function test_forgot_password_returns_202(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_forgot_password_unknown_email_also_returns_202(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // POST /auth/reset-password
    // -------------------------------------------------------------------------

    public function test_reset_password_with_valid_token(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // POST /auth/change-password
    // -------------------------------------------------------------------------

    public function test_change_password_preserves_current_session(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // GET /auth/profile
    // -------------------------------------------------------------------------

    public function test_profile_returns_authenticated_user_data(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // PUT /auth/profile
    // -------------------------------------------------------------------------

    public function test_update_profile_modifies_allowed_fields(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_update_profile_rejects_forbidden_fields(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // GET /auth/login-history
    // -------------------------------------------------------------------------

    public function test_login_history_scoped_to_authenticated_user(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // GET /auth/devices
    // -------------------------------------------------------------------------

    public function test_devices_scoped_to_authenticated_user(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // DELETE /auth/devices/{deviceId}
    // -------------------------------------------------------------------------

    public function test_revoke_own_device_succeeds(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_revoke_another_users_device_returns_404(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    // -------------------------------------------------------------------------
    // Cross-User Security
    // -------------------------------------------------------------------------

    public function test_cannot_access_another_users_profile(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_cannot_access_another_users_login_history(): void
    {
        $this->markTestSkipped('PLANNED');
    }

    public function test_cannot_access_another_users_devices(): void
    {
        $this->markTestSkipped('PLANNED');
    }
}
