<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Providers;

use App\Domains\Laboratory\Interfaces\LaboratoryRepositoryInterface;
use App\Domains\Laboratory\Interfaces\LaboratoryServiceInterface;
use App\Domains\Laboratory\Repositories\LaboratoryRepository;
use App\Domains\Laboratory\Services\LaboratoryService;
use Illuminate\Support\ServiceProvider;

final class LaboratoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(LaboratoryRepositoryInterface::class, LaboratoryRepository::class);
        $this->app->bind(LaboratoryServiceInterface::class, LaboratoryService::class);
    }
}