<?php

declare(strict_types=1);

namespace App\Domains\EMR\Providers;

use App\Domains\EMR\Interfaces\EMRRepositoryInterface;
use App\Domains\EMR\Interfaces\EMRServiceInterface;
use App\Domains\EMR\Repositories\EMRRepository;
use App\Domains\EMR\Services\EMRService;
use Illuminate\Support\ServiceProvider;

final class EMRServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(EMRRepositoryInterface::class, EMRRepository::class);
        $this->app->bind(EMRServiceInterface::class, EMRService::class);
    }
}