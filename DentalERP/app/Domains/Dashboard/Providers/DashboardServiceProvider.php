<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Providers;

use App\Domains\Dashboard\Interfaces\DashboardRepositoryInterface;
use App\Domains\Dashboard\Interfaces\DashboardServiceInterface;
use App\Domains\Dashboard\Repositories\DashboardRepository;
use App\Domains\Dashboard\Services\DashboardService;
use Illuminate\Support\ServiceProvider;

final class DashboardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
    }
}