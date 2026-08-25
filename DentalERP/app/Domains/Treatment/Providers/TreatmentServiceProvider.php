<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Providers;

use App\Domains\Treatment\Interfaces\TreatmentRepositoryInterface;
use App\Domains\Treatment\Interfaces\TreatmentServiceInterface;
use App\Domains\Treatment\Repositories\TreatmentRepository;
use App\Domains\Treatment\Services\TreatmentService;
use Illuminate\Support\ServiceProvider;

final class TreatmentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(TreatmentRepositoryInterface::class, TreatmentRepository::class);
        $this->app->bind(TreatmentServiceInterface::class, TreatmentService::class);
    }
}