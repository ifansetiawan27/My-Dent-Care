<?php

declare(strict_types=1);

namespace App\Domains\Finance\Providers;

use App\Domains\Finance\Interfaces\ChartOfAccountRepositoryInterface;
use App\Domains\Finance\Interfaces\ChartOfAccountServiceInterface;
use App\Domains\Finance\Interfaces\FinancialReportRepositoryInterface;
use App\Domains\Finance\Interfaces\FinancialReportServiceInterface;
use App\Domains\Finance\Interfaces\JournalEntryRepositoryInterface;
use App\Domains\Finance\Interfaces\JournalEntryServiceInterface;
use App\Domains\Finance\Repositories\ChartOfAccountRepository;
use App\Domains\Finance\Repositories\FinancialReportRepository;
use App\Domains\Finance\Repositories\JournalEntryRepository;
use App\Domains\Finance\Services\ChartOfAccountService;
use App\Domains\Finance\Services\FinancialReportService;
use App\Domains\Finance\Services\JournalEntryService;
use Illuminate\Support\ServiceProvider;

final class FinanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }

    public function register(): void
    {
        $this->app->bind(ChartOfAccountRepositoryInterface::class, ChartOfAccountRepository::class);
        $this->app->bind(ChartOfAccountServiceInterface::class, ChartOfAccountService::class);

        $this->app->bind(JournalEntryRepositoryInterface::class, JournalEntryRepository::class);
        $this->app->bind(JournalEntryServiceInterface::class, JournalEntryService::class);

        $this->app->bind(FinancialReportRepositoryInterface::class, FinancialReportRepository::class);
        $this->app->bind(FinancialReportServiceInterface::class, FinancialReportService::class);
    }
}
