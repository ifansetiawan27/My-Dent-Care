<?php
declare(strict_types=1);
namespace App\Domains\Employee\Providers;
use Illuminate\Support\ServiceProvider;
final class EmployeeServiceProvider extends ServiceProvider {
    public function boot(): void { $this->loadRoutesFrom(__DIR__.'/../Routes/api.php'); $this->loadMigrationsFrom(__DIR__.'/../Migrations'); }
}
