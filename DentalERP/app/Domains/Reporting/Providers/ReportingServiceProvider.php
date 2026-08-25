<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Providers;

use App\Domains\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domains\Reporting\Interfaces\ReportingServiceInterface;
use App\Domains\Reporting\Repositories\ReportingRepository;
use App\Domains\Reporting\Services\ReportingService;
use Illuminate\Support\ServiceProvider;

final class ReportingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(ReportingRepositoryInterface::class, ReportingRepository::class);
        $this->app->bind(ReportingServiceInterface::class, ReportingService::class);
    }
}