<?php

declare(strict_types=1);

namespace App\Domains\Employee\Providers;

use App\Domains\Employee\Interfaces\EmployeeRepositoryInterface;
use App\Domains\Employee\Interfaces\EmployeeServiceInterface;
use App\Domains\Employee\Repositories\EmployeeRepository;
use App\Domains\Employee\Services\EmployeeService;
use Illuminate\Support\ServiceProvider;

final class EmployeeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(EmployeeServiceInterface::class, EmployeeService::class);
    }
}