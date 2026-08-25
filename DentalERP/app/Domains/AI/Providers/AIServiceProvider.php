<?php

declare(strict_types=1);

namespace App\Domains\AI\Providers;

use App\Domains\AI\Interfaces\AIRepositoryInterface;
use App\Domains\AI\Interfaces\AIServiceInterface;
use App\Domains\AI\Repositories\AIRepository;
use App\Domains\AI\Services\AIService;
use Illuminate\Support\ServiceProvider;

final class AIServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(AIRepositoryInterface::class, AIRepository::class);
        $this->app->bind(AIServiceInterface::class, AIService::class);
    }
}