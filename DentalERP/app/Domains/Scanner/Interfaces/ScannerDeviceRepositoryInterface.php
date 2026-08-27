<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Interfaces;
use App\Domains\Scanner\Models\ScannerDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface ScannerDeviceRepositoryInterface {
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id): ?ScannerDevice;
    public function create(array $data): ScannerDevice;
    public function update(ScannerDevice $device, array $data): ScannerDevice;
    public function delete(ScannerDevice $device): bool;
}
