<?php declare(strict_types=1); namespace App\Domains\Appointment\Providers; use Illuminate\Support\ServiceProvider;
final class AppointmentServiceProvider extends ServiceProvider { public function boot(): void { $this->loadRoutesFrom(__DIR__.'/../Routes/api.php'); $this->loadMigrationsFrom(__DIR__.'/../Migrations'); } }
