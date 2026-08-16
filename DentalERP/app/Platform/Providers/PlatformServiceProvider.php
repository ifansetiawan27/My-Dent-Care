<?php

declare(strict_types=1);

namespace App\Platform\Providers;

use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\Audit\Services\AuditService;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use App\Platform\FileStorage\Services\FileStorageService;
use App\Platform\Logging\Contracts\LoggerServiceInterface;
use App\Platform\Logging\Services\LoggerService;
use App\Platform\Notification\Contracts\NotificationServiceInterface;
use App\Platform\Notification\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

final class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditServiceInterface::class, AuditService::class);
        $this->app->bind(FileStorageServiceInterface::class, FileStorageService::class);
        $this->app->bind(LoggerServiceInterface::class, LoggerService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Audit/Migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../FileStorage/Migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../Logging/Migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../Notification/Migrations');
    }
}
