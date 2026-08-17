<?php

declare(strict_types=1);

namespace App\Domains\Asset\Providers;

use App\Domains\Asset\Interfaces\AssetRepositoryInterface;
use App\Domains\Asset\Interfaces\AssetServiceInterface;
use App\Domains\Asset\Repositories\AssetRepository;
use App\Domains\Asset\Services\AssetService;
use Illuminate\Support\ServiceProvider;

final class AssetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(AssetRepositoryInterface::class, AssetRepository::class);
        $this->app->bind(AssetServiceInterface::class, AssetService::class);
    }
}