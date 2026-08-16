<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Providers;

use App\Domains\Odontogram\Interfaces\OdontogramRepositoryInterface;
use App\Domains\Odontogram\Interfaces\OdontogramServiceInterface;
use App\Domains\Odontogram\Repositories\OdontogramRepository;
use App\Domains\Odontogram\Services\OdontogramService;
use Illuminate\Support\ServiceProvider;

final class OdontogramServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(OdontogramRepositoryInterface::class, OdontogramRepository::class);
        $this->app->bind(OdontogramServiceInterface::class, OdontogramService::class);
    }
}