<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Authentication\Services;

use App\Core\Exceptions\BusinessException;
use App\Domains\Authentication\DTO\LoginDTO;
use App\Domains\Authentication\Enums\DeviceType;
use App\Domains\Authentication\Enums\LoginStatus;
use App\Domains\Authentication\Interfaces\AuthRepositoryInterface;
use App\Domains\Authentication\Interfaces\LockoutServiceInterface;
use App\Domains\Authentication\Models\UserDevice;
use App\Domains\Authentication\Models\UserSession;
use App\Domains\Authentication\Services\AuthService;
use App\Domains\Branch\Models\Branch;
use App\Domains\Organization\Models\Organization;
use App\Domains\Organization\Enums\OrganizationStatus;
use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Models\User;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $service;
    private MockInterface $repository;
    private MockInterface $lockoutService;
    private MockInterface $fileStorage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository    = Mockery::mock(AuthRepositoryInterface::class);
        $this->lockoutService = Mockery::mock(LockoutServiceInterface::class);
        $this->fileStorage   = Mockery::mock(FileStorageServiceInterface::class);
        $this->service       = new AuthService($this->repository, $this->lockoutService, $this->fileStorage);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUser(array $attributes = []): User
    {
        $user = new User();
        $user->forceFill(array_merge([
            'id'              => (string) Str::orderedUuid(),
            'organization_id' => (string) Str::orderedUuid(),
            'branch_id'       => (string) Str::orderedUuid(),
            'username'        => 'testuser',
            'email'           => 'test@example.com',
            'name'            => 'Test User',
            'password'        => bcrypt('password'),
            'status'          => UserStatus::Active,
        ], $attributes));

        return $user;
    }

    private function makeDevice(): UserDevice
    {
        $device = new UserDevice();
        $device->forceFill([
            'id'         => (string) Str::orderedUuid(),
            'user_id'    => (string) Str::orderedUuid(),
            'device_uuid'=> 'device-uuid-123',
            'device_name'=> 'Chrome on Windows',
            'device_type'=> DeviceType::Web->value,
            'last_login_at' => now(),
            'last_activity_at' => now(),
        ]);

        return $device;
    }

    private function makeSession(string $userId): UserSession
    {
        $session = new UserSession();
        $session->forceFill([
            'id'              => (string) Str::orderedUuid(),
            'user_id'         => $userId,
            'organization_id' => (string) Str::orderedUuid(),
            'branch_id'       => (string) Str::orderedUuid(),
            'user_device_id'  => (string) Str::orderedUuid(),
            'started_at'      => now(),
            'expires_at'      => now()->addMinutes(60),
        ]);

        return $session;
    }

    private function makeLoginDTO(): LoginDTO
    {
        return new LoginDTO(
            identifier:     'testuser',
            password:       'password',
            organizationId: (string) Str::orderedUuid(),
            branchId:       (string) Str::orderedUuid(),
            deviceUuid:     'device-uuid-123',
            deviceName:     'Chrome on Windows',
            deviceType:     'web',
        );
    }

    // -------------------------------------------------------------------------
    // Login Tests
    // -------------------------------------------------------------------------

    public function test_login_success_returns_token_pair_and_user_data(): void
    {
        // This test requires DB transaction support.
        // Marked as PLANNED until integration environment is available.

        $this->markTestSkipped('Requires DB transaction support and running application.');
    }

    // -------------------------------------------------------------------------
    // Change Password Tests — DD-AUTH-004 / DD-AUTH-018
    // -------------------------------------------------------------------------

    public function test_change_password_preserves_current_session(): void
    {
        $this->markTestSkipped('Requires database and Sanctum authentication context.');
    }

    public function test_change_password_revokes_other_sessions(): void
    {
        $this->markTestSkipped('Requires database and Sanctum authentication context.');
    }

    // -------------------------------------------------------------------------
    // Logout Tests
    // -------------------------------------------------------------------------

    public function test_logout_revokes_current_session(): void
    {
        $this->markTestSkipped('Requires database and Sanctum authentication context.');
    }

    public function test_logout_all_revokes_all_user_sessions(): void
    {
        $this->markTestSkipped('Requires database and Sanctum authentication context.');
    }

    // -------------------------------------------------------------------------
    // Forgot / Reset Password Tests
    // -------------------------------------------------------------------------

    public function test_forgot_password_returns_generic_response_for_non_existent_user(): void
    {
        $this->markTestSkipped('Requires running application with Password Broker.');
    }

    public function test_reset_password_revokes_all_sessions(): void
    {
        $this->markTestSkipped('Requires running application with Password Broker and database.');
    }
}
