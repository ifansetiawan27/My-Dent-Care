<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Providers;

use Illuminate\Support\ServiceProvider;

final class MasterDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Domains\MasterData\Helpers\ResourceResolver::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
