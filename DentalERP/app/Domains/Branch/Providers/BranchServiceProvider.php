<?php

declare(strict_types=1);

namespace App\Domains\Branch\Providers;

use App\Domains\Branch\Interfaces\BranchRepositoryInterface;
use App\Domains\Branch\Interfaces\BranchServiceInterface;
use App\Domains\Branch\Repositories\BranchRepository;
use App\Domains\Branch\Services\BranchService;
use Illuminate\Support\ServiceProvider;

final class BranchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(BranchServiceInterface::class, BranchService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
