<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            base_path('app/Domains/Branch/Routes/api.php'),
            base_path('app/Domains/Authentication/Routes/api.php'),
        ],
        health: '/up',
    )
    ->withProviders([
        App\Platform\Providers\PlatformServiceProvider::class,
    ])
    ->withExceptions()
    ->create();
