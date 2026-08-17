<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Providers;

use App\Domains\IntegrationHub\Interfaces\IntegrationHubRepositoryInterface;
use App\Domains\IntegrationHub\Interfaces\IntegrationHubServiceInterface;
use App\Domains\IntegrationHub\Repositories\IntegrationHubRepository;
use App\Domains\IntegrationHub\Services\IntegrationHubService;
use Illuminate\Support\ServiceProvider;

final class IntegrationHubServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(IntegrationHubRepositoryInterface::class, IntegrationHubRepository::class);
        $this->app->bind(IntegrationHubServiceInterface::class, IntegrationHubService::class);
    }
}