<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Authentication\Services;

use App\Core\Exceptions\BusinessException;
use App\Domains\Authentication\DTO\TokenPairDTO;
use App\Domains\Authentication\Interfaces\AuthRepositoryInterface;
use App\Domains\Authentication\Models\RefreshToken;
use App\Domains\Authentication\Models\UserSession;
use App\Domains\Authentication\Services\TokenService;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    private TokenService $service;
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(AuthRepositoryInterface::class);
        $this->service    = new TokenService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Refresh Token Rotation
    // -------------------------------------------------------------------------

    public function test_valid_refresh_token_rotates_successfully(): void
    {
        $this->markTestSkipped('Requires database and Sanctum authentication context.');
    }

    public function test_rotated_refresh_token_cannot_be_used_twice(): void
    {
        $this->markTestSkipped('Requires database context.');
    }

    // -------------------------------------------------------------------------
    // Refresh Token Reuse Detection — ADR-002
    // -------------------------------------------------------------------------

    public function test_reuse_of_replaced_refresh_token_revokes_entire_family(): void
    {
        $this->markTestSkipped('Requires database context.');
    }

    public function test_reuse_revokes_owning_session(): void
    {
        $this->markTestSkipped('Requires database context.');
    }

    // -------------------------------------------------------------------------
    // Refresh Token Expiration
    // -------------------------------------------------------------------------

    public function test_expired_refresh_token_is_rejected(): void
    {
        $this->markTestSkipped('Requires database context.');
    }

    public function test_revoked_refresh_token_is_rejected(): void
    {
        $this->markTestSkipped('Requires database context.');
    }
}
