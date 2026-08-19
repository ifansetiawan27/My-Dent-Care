<?php

declare(strict_types=1);

namespace App\Domains\Branch\Providers;

use Illuminate\Support\ServiceProvider;

final class BranchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
