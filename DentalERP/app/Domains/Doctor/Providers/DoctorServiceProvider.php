<?php

declare(strict_types=1);

namespace App\Domains\Doctor\Providers;

use App\Domains\Doctor\Interfaces\DoctorRepositoryInterface;
use App\Domains\Doctor\Interfaces\DoctorServiceInterface;
use App\Domains\Doctor\Repositories\DoctorRepository;
use App\Domains\Doctor\Services\DoctorService;
use Illuminate\Support\ServiceProvider;

final class DoctorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(DoctorRepositoryInterface::class, DoctorRepository::class);
        $this->app->bind(DoctorServiceInterface::class, DoctorService::class);
    }
}