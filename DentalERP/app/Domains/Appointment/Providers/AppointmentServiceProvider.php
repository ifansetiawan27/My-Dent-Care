<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Providers;

use App\Domains\Appointment\Interfaces\AppointmentRepositoryInterface;
use App\Domains\Appointment\Interfaces\AppointmentServiceInterface;
use App\Domains\Appointment\Repositories\AppointmentRepository;
use App\Domains\Appointment\Services\AppointmentService;
use Illuminate\Support\ServiceProvider;

final class AppointmentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(AppointmentRepositoryInterface::class, AppointmentRepository::class);
        $this->app->bind(AppointmentServiceInterface::class, AppointmentService::class);
    }
}