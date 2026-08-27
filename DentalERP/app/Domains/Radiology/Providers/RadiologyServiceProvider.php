<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Providers;

use App\Domains\Radiology\Interfaces\RadiologyImageRepositoryInterface;
use App\Domains\Radiology\Interfaces\RadiologyImageServiceInterface;
use App\Domains\Radiology\Interfaces\RadiologyOrderRepositoryInterface;
use App\Domains\Radiology\Interfaces\RadiologyOrderServiceInterface;
use App\Domains\Radiology\Interfaces\RadiologyReportRepositoryInterface;
use App\Domains\Radiology\Interfaces\RadiologyReportServiceInterface;
use App\Domains\Radiology\Repositories\RadiologyImageRepository;
use App\Domains\Radiology\Repositories\RadiologyOrderRepository;
use App\Domains\Radiology\Repositories\RadiologyReportRepository;
use App\Domains\Radiology\Services\RadiologyImageService;
use App\Domains\Radiology\Services\RadiologyOrderService;
use App\Domains\Radiology\Services\RadiologyReportService;
use Illuminate\Support\ServiceProvider;

final class RadiologyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(RadiologyOrderRepositoryInterface::class, RadiologyOrderRepository::class);
        $this->app->bind(RadiologyOrderServiceInterface::class, RadiologyOrderService::class);
        $this->app->bind(RadiologyImageRepositoryInterface::class, RadiologyImageRepository::class);
        $this->app->bind(RadiologyImageServiceInterface::class, RadiologyImageService::class);
        $this->app->bind(RadiologyReportRepositoryInterface::class, RadiologyReportRepository::class);
        $this->app->bind(RadiologyReportServiceInterface::class, RadiologyReportService::class);
    }
}
