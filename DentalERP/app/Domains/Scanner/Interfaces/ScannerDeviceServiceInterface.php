<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Interfaces;
use App\Domains\Scanner\DTO\CreateScannerDeviceDTO;
use App\Domains\Scanner\DTO\UpdateScannerDeviceDTO;
use App\Domains\Scanner\Models\ScannerDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface ScannerDeviceServiceInterface {
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id): ScannerDevice;
    public function create(CreateScannerDeviceDTO $dto): ScannerDevice;
    public function update(string $id, UpdateScannerDeviceDTO $dto): ScannerDevice;
    public function delete(string $id): bool;
    public function calibrateDevice(string $id): ScannerDevice;
}
