<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            base_path('app/Domains/Branch/Routes/api.php'),
            base_path('app/Domains/Authentication/Routes/api.php'),
            base_path('app/Domains/Organization/Routes/settings.php'),
            base_path('app/Domains/AI/Routes/api.php'),
        ],
        health: '/up',
    )
    ->withProviders([
        App\Platform\Providers\PlatformServiceProvider::class,
        App\Domains\Organization\Providers\OrganizationServiceProvider::class,
        App\Domains\Branch\Providers\BranchServiceProvider::class,
        App\Domains\RolePermission\Providers\RolePermissionServiceProvider::class,
        App\Domains\MasterData\Providers\MasterDataServiceProvider::class,
        App\Domains\Employee\Providers\EmployeeServiceProvider::class,
        App\Domains\Doctor\Providers\DoctorServiceProvider::class,
        App\Domains\Patient\Providers\PatientServiceProvider::class,
        App\Domains\Appointment\Providers\AppointmentServiceProvider::class,
        App\Domains\Odontogram\Providers\OdontogramServiceProvider::class,
        App\Domains\Treatment\Providers\TreatmentServiceProvider::class,
        App\Domains\EMR\Providers\EMRServiceProvider::class,
        App\Domains\Subscription\Providers\SubscriptionServiceProvider::class,
        App\Domains\Billing\Providers\BillingServiceProvider::class,
        App\Domains\Inventory\Providers\InventoryServiceProvider::class,
        App\Domains\Pharmacy\Providers\PharmacyServiceProvider::class,
        App\Domains\Laboratory\Providers\LaboratoryServiceProvider::class,
        App\Domains\Procurement\Providers\ProcurementServiceProvider::class,
        App\Domains\Asset\Providers\AssetServiceProvider::class,
        App\Domains\HR\Providers\HRServiceProvider::class,
        App\Domains\CRM\Providers\CRMServiceProvider::class,
        App\Domains\Reporting\Providers\ReportingServiceProvider::class,
        App\Domains\Dashboard\Providers\DashboardServiceProvider::class,
        App\Domains\IntegrationHub\Providers\IntegrationHubServiceProvider::class,
        App\Domains\AI\Providers\AIServiceProvider::class,
    ])
    ->withExceptions()
    ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->job(App\Domains\Subscription\Jobs\ProcessTrialExpiration::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\ProcessSubscriptionRenewals::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\RetryFailedSubscriptionPayment::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\ProcessGraceExpiration::class)->everyFifteenMinutes();
    })
    ->create();
