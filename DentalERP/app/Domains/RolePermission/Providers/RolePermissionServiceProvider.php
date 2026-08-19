<?php

declare(strict_types=1);

namespace App\Domains\RolePermission\Providers;

use Illuminate\Support\ServiceProvider;

final class RolePermissionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
