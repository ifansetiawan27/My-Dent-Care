<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Providers;
use App\Domains\Scanner\Interfaces\ScannerDeviceRepositoryInterface;
use App\Domains\Scanner\Interfaces\ScannerDeviceServiceInterface;
use App\Domains\Scanner\Interfaces\ScanSessionRepositoryInterface;
use App\Domains\Scanner\Interfaces\ScanSessionServiceInterface;
use App\Domains\Scanner\Interfaces\ScanFileRepositoryInterface;
use App\Domains\Scanner\Interfaces\ScanFileServiceInterface;
use App\Domains\Scanner\Repositories\ScannerDeviceRepository;
use App\Domains\Scanner\Repositories\ScanSessionRepository;
use App\Domains\Scanner\Repositories\ScanFileRepository;
use App\Domains\Scanner\Services\ScannerDeviceService;
use App\Domains\Scanner\Services\ScanSessionService;
use App\Domains\Scanner\Services\ScanFileService;
use Illuminate\Support\ServiceProvider;
final class ScannerServiceProvider extends ServiceProvider {
    public function boot(): void { $this->loadMigrationsFrom(__DIR__ . '/../Migrations'); }
    public function register(): void {
        $this->app->bind(ScannerDeviceRepositoryInterface::class, ScannerDeviceRepository::class);
        $this->app->bind(ScannerDeviceServiceInterface::class, ScannerDeviceService::class);
        $this->app->bind(ScanSessionRepositoryInterface::class, ScanSessionRepository::class);
        $this->app->bind(ScanSessionServiceInterface::class, ScanSessionService::class);
        $this->app->bind(ScanFileRepositoryInterface::class, ScanFileRepository::class);
        $this->app->bind(ScanFileServiceInterface::class, ScanFileService::class);
    }
}
