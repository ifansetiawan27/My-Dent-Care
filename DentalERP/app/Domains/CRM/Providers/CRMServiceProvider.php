<?php

declare(strict_types=1);

namespace App\Domains\CRM\Providers;

use App\Domains\CRM\Interfaces\CRMRepositoryInterface;
use App\Domains\CRM\Interfaces\CRMServiceInterface;
use App\Domains\CRM\Repositories\CRMRepository;
use App\Domains\CRM\Services\CRMService;
use Illuminate\Support\ServiceProvider;

final class CRMServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(CRMRepositoryInterface::class, CRMRepository::class);
        $this->app->bind(CRMServiceInterface::class, CRMService::class);
    }
}