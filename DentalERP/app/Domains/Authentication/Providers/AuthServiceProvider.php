<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Providers;

use App\Domains\Authentication\Interfaces\AuthRepositoryInterface;
use App\Domains\Authentication\Interfaces\AuthServiceInterface;
use App\Domains\Authentication\Interfaces\LockoutServiceInterface;
use App\Domains\Authentication\Interfaces\TokenServiceInterface;
use App\Domains\Authentication\Repositories\AuthRepository;
use App\Domains\Authentication\Services\AuthService;
use App\Domains\Authentication\Services\LockoutService;
use App\Domains\Authentication\Services\TokenService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(TokenServiceInterface::class, TokenService::class);
        $this->app->bind(LockoutServiceInterface::class, LockoutService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
