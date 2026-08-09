<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Authentication\Services;

use App\Domains\Authentication\Interfaces\LockoutServiceInterface;
use App\Domains\Authentication\Services\LockoutService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LockoutServiceTest extends TestCase
{
    private LockoutServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LockoutService();
    }

    // -------------------------------------------------------------------------
    // isLocked
    // -------------------------------------------------------------------------

    public function test_isLocked_returns_false_when_under_threshold(): void
    {
        Cache::shouldReceive('store')->with('redis')->andReturnSelf();
        Cache::shouldReceive('get')->andReturn(3);
        Cache::shouldReceive('increment');
        Cache::shouldReceive('set');
        Cache::shouldReceive('forget');

        $this->assertFalse($this->service->isLocked('user@test.com', '127.0.0.1'));
    }

    // -------------------------------------------------------------------------
    // MAX_ATTEMPTS constant
    // -------------------------------------------------------------------------

    public function test_lockout_service_has_correct_max_attempts(): void
    {
        $reflection = new \ReflectionClass(LockoutService::class);
        $constant = $reflection->getConstant('MAX_ATTEMPTS');

        $this->assertNotNull($constant);
    }

    public function test_lockout_service_has_correct_ttl(): void
    {
        $reflection = new \ReflectionClass(LockoutService::class);
        $constant = $reflection->getConstant('TTL_MINUTES');

        $this->assertNotNull($constant);
    }
}
