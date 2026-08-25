<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Providers;

use App\Domains\Procurement\Interfaces\ProcurementRepositoryInterface;
use App\Domains\Procurement\Interfaces\ProcurementServiceInterface;
use App\Domains\Procurement\Repositories\ProcurementRepository;
use App\Domains\Procurement\Services\ProcurementService;
use Illuminate\Support\ServiceProvider;

final class ProcurementServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(ProcurementRepositoryInterface::class, ProcurementRepository::class);
        $this->app->bind(ProcurementServiceInterface::class, ProcurementService::class);
    }
}