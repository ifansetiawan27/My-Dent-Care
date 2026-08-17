<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Providers;

use App\Domains\Pharmacy\Interfaces\PharmacyRepositoryInterface;
use App\Domains\Pharmacy\Interfaces\PharmacyServiceInterface;
use App\Domains\Pharmacy\Repositories\PharmacyRepository;
use App\Domains\Pharmacy\Services\PharmacyService;
use Illuminate\Support\ServiceProvider;

final class PharmacyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(PharmacyRepositoryInterface::class, PharmacyRepository::class);
        $this->app->bind(PharmacyServiceInterface::class, PharmacyService::class);
    }
}