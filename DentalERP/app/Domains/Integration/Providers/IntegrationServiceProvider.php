<?php

declare(strict_types=1);

namespace App\Domains\Integration\Providers;

use App\Domains\Integration\Interfaces\IntegrationConfigRepositoryInterface;
use App\Domains\Integration\Interfaces\IntegrationConfigServiceInterface;
use App\Domains\Integration\Interfaces\IntegrationLogRepositoryInterface;
use App\Domains\Integration\Interfaces\IntegrationLogServiceInterface;
use App\Domains\Integration\Interfaces\IntegrationMappingRepositoryInterface;
use App\Domains\Integration\Interfaces\IntegrationMappingServiceInterface;
use App\Domains\Integration\Repositories\IntegrationConfigRepository;
use App\Domains\Integration\Repositories\IntegrationLogRepository;
use App\Domains\Integration\Repositories\IntegrationMappingRepository;
use App\Domains\Integration\Services\IntegrationConfigService;
use App\Domains\Integration\Services\IntegrationLogService;
use App\Domains\Integration\Services\IntegrationMappingService;
use Illuminate\Support\ServiceProvider;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(IntegrationConfigRepositoryInterface::class, IntegrationConfigRepository::class);
        $this->app->bind(IntegrationConfigServiceInterface::class, IntegrationConfigService::class);
        $this->app->bind(IntegrationLogRepositoryInterface::class, IntegrationLogRepository::class);
        $this->app->bind(IntegrationLogServiceInterface::class, IntegrationLogService::class);
        $this->app->bind(IntegrationMappingRepositoryInterface::class, IntegrationMappingRepository::class);
        $this->app->bind(IntegrationMappingServiceInterface::class, IntegrationMappingService::class);
    }
}
