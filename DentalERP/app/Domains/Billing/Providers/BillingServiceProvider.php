<?php

declare(strict_types=1);

namespace App\Domains\Billing\Providers;

use App\Domains\Billing\Interfaces\BillingRepositoryInterface;
use App\Domains\Billing\Interfaces\BillingServiceInterface;
use App\Domains\Billing\Repositories\BillingRepository;
use App\Domains\Billing\Services\BillingService;
use Illuminate\Support\ServiceProvider;

final class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(BillingRepositoryInterface::class, BillingRepository::class);
        $this->app->bind(BillingServiceInterface::class, BillingService::class);
    }
}