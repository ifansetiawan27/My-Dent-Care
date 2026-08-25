<?php

declare(strict_types=1);

namespace App\Domains\Patient\Providers;

use App\Domains\Patient\Interfaces\PatientRepositoryInterface;
use App\Domains\Patient\Interfaces\PatientServiceInterface;
use App\Domains\Patient\Repositories\PatientRepository;
use App\Domains\Patient\Services\PatientService;
use Illuminate\Support\ServiceProvider;

final class PatientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(PatientServiceInterface::class, PatientService::class);
    }
}