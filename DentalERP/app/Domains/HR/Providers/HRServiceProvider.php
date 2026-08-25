<?php

declare(strict_types=1);

namespace App\Domains\HR\Providers;

use App\Domains\HR\Interfaces\HRRepositoryInterface;
use App\Domains\HR\Interfaces\HRServiceInterface;
use App\Domains\HR\Repositories\HRRepository;
use App\Domains\HR\Services\HRService;
use Illuminate\Support\ServiceProvider;

final class HRServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(HRRepositoryInterface::class, HRRepository::class);
        $this->app->bind(HRServiceInterface::class, HRService::class);
    }
}