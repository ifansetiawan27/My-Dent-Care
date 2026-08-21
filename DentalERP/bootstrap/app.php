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
    ->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions): void {
        // This application only serves an API, so every error must be rendered
        // as JSON regardless of the client's Accept header.
        $exceptions->shouldRenderJsonWhen(
            static fn (): bool => true,
        );
    })
    ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware): void {
        // Laravel defaults to redirecting guests to route('login'), which this
        // API does not define. Without this, an unauthenticated request that
        // omits `Accept: application/json` fails with a 500
        // "Route [login] not defined" instead of a 401.
        $middleware->redirectGuestsTo(static fn (): ?string => null);

        // Applied globally rather than to the api group so that non-grouped
        // routes, such as the /up health endpoint, are hardened too.
        $middleware->prepend(\App\Platform\Http\Middleware\SecurityHeaders::class);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->job(App\Domains\Subscription\Jobs\ProcessTrialExpiration::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\ProcessSubscriptionRenewals::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\RetryFailedSubscriptionPayment::class)->everyFifteenMinutes();
        $schedule->job(App\Domains\Subscription\Jobs\ProcessGraceExpiration::class)->everyFifteenMinutes();
    })
    ->create();
