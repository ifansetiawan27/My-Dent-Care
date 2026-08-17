<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Providers;

use App\Domains\Inventory\Interfaces\InventoryRepositoryInterface;
use App\Domains\Inventory\Interfaces\InventoryServiceInterface;
use App\Domains\Inventory\Repositories\InventoryRepository;
use App\Domains\Inventory\Services\InventoryService;
use Illuminate\Support\ServiceProvider;

final class InventoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
    }
}