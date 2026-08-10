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
        App\Domains\MasterData\Providers\MasterDataServiceProvider::class,
        App\Domains\Employee\Providers\EmployeeServiceProvider::class,
        App\Domains\Doctor\Providers\DoctorServiceProvider::class,
        App\Domains\Patient\Providers\PatientServiceProvider::class,
        App\Domains\Appointment\Providers\AppointmentServiceProvider::class,
        App\Domains\Subscription\Providers\SubscriptionServiceProvider::class,
    ])
    ->withExceptions()
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->job(App\Domains\Subscription\Jobs\ProcessTrialExpiration::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\ProcessSubscriptionRenewals::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\RetryFailedSubscriptionPayment::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\ProcessGraceExpiration::class)->everyFifteenMinutes();
    })
    ->create();
