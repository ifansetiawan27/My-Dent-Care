<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Authentication\Models;

use App\Domains\Authentication\Models\RefreshToken;
use App\Domains\Authentication\Models\UserDevice;
use App\Domains\Authentication\Models\UserSession;
use App\Domains\Authentication\Models\LoginHistory;
use App\Domains\Authentication\Enums\DeviceType;
use App\Domains\Authentication\Enums\LoginStatus;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

/**
 * Foundation unit tests for Authentication models.
 * Validates casts, hidden fields, relationships, and structural conformance
 * to frozen ERD (no database required — pure model inspection).
 */
class AuthenticationModelTest extends TestCase
{
    // -------------------------------------------------------------------------
    // UserDevice
    // -------------------------------------------------------------------------

    public function test_user_device_casts_match_erd(): void
    {
        $model = new UserDevice();

        $casts = $model->getCasts();

        $this->assertSame(DeviceType::class, $casts['device_type']);
        $this->assertSame('boolean', $casts['is_trusted']);
        $this->assertSame('datetime', $casts['last_login_at']);
        $this->assertSame('datetime', $casts['last_activity_at']);
        $this->assertSame('datetime', $casts['revoked_at']);
        $this->assertSame('datetime', $casts['created_at']);
        $this->assertSame('datetime', $casts['updated_at']);
    }

    public function test_user_device_table_name(): void
    {
        $model = new UserDevice();

        $this->assertSame('user_devices', $model->getTable());
    }

    public function test_user_device_does_not_use_soft_deletes(): void
    {
        $model = new UserDevice();

        $this->assertNotContains('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model));
    }

    // -------------------------------------------------------------------------
    // UserSession
    // -------------------------------------------------------------------------

    public function test_user_session_casts_match_erd(): void
    {
        $model = new UserSession();

        $casts = $model->getCasts();

        $this->assertSame('datetime', $casts['started_at']);
        $this->assertSame('datetime', $casts['expires_at']);
        $this->assertSame('datetime', $casts['revoked_at']);
        $this->assertSame('datetime', $casts['created_at']);
        $this->assertSame('datetime', $casts['updated_at']);
    }

    public function test_user_session_table_name(): void
    {
        $model = new UserSession();

        $this->assertSame('user_sessions', $model->getTable());
    }

    public function test_user_session_does_not_use_soft_deletes(): void
    {
        $model = new UserSession();

        $this->assertNotContains('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model));
    }

    // -------------------------------------------------------------------------
    // RefreshToken
    // -------------------------------------------------------------------------

    public function test_refresh_token_casts_match_erd(): void
    {
        $model = new RefreshToken();

        $casts = $model->getCasts();

        $this->assertSame('datetime', $casts['expires_at']);
        $this->assertSame('datetime', $casts['last_used_at']);
        $this->assertSame('datetime', $casts['revoked_at']);
        $this->assertSame('datetime', $casts['created_at']);
        $this->assertSame('datetime', $casts['updated_at']);
    }

    public function test_refresh_token_table_name(): void
    {
        $model = new RefreshToken();

        $this->assertSame('refresh_tokens', $model->getTable());
    }

    public function test_refresh_token_hides_token_hash(): void
    {
        $model = new RefreshToken();

        $hidden = $model->getHidden();

        $this->assertContains('token_hash', $hidden);
    }

    public function test_refresh_token_does_not_use_soft_deletes(): void
    {
        $model = new RefreshToken();

        $this->assertNotContains('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model));
    }

    // -------------------------------------------------------------------------
    // LoginHistory
    // -------------------------------------------------------------------------

    public function test_login_history_casts_match_erd(): void
    {
        $model = new LoginHistory();

        $casts = $model->getCasts();

        $this->assertSame(LoginStatus::class, $casts['login_status']);
        $this->assertSame('datetime', $casts['login_at']);
        $this->assertSame('datetime', $casts['logout_at']);
        $this->assertSame('datetime', $casts['created_at']);
    }

    public function test_login_history_table_name(): void
    {
        $model = new LoginHistory();

        $this->assertSame('login_histories', $model->getTable());
    }

    public function test_login_history_has_no_updated_at_column(): void
    {
        $model = new LoginHistory();

        $this->assertNull($model->getUpdatedAtColumn());
    }

    public function test_login_history_does_not_use_soft_deletes(): void
    {
        $model = new LoginHistory();

        $this->assertNotContains('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model));
    }

    // -------------------------------------------------------------------------
    // UUID trait presence
    // -------------------------------------------------------------------------

    public function test_all_auth_models_use_has_uuid_trait(): void
    {
        $models = [UserDevice::class, UserSession::class, RefreshToken::class, LoginHistory::class];

        foreach ($models as $modelClass) {
            $traits = class_uses_recursive($modelClass);
            $this->assertContains('App\Core\Traits\HasUuid', $traits, "{$modelClass} must use HasUuid trait");
        }
    }
}
