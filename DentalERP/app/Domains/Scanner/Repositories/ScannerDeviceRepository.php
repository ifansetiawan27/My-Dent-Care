<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Repositories;
use App\Domains\Scanner\Interfaces\ScannerDeviceRepositoryInterface;
use App\Domains\Scanner\Models\ScannerDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
final class ScannerDeviceRepository implements ScannerDeviceRepositoryInterface {
    public function paginate(array $filters): LengthAwarePaginator {
        $query = ScannerDevice::query();
        if (!empty($filters['status'])) { $query->where('status', $filters['status']); }
        if (!empty($filters['manufacturer'])) { $query->where('manufacturer', $filters['manufacturer']); }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('device_name', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('serial_number', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('model', 'ILIKE', "%{$filters['search']}%");
            });
        }
        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'last_calibration_at']) ? $filters['sort_by'] : 'created_at';
        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')->paginate(min((int)($filters['per_page'] ?? 20), 100));
    }
    public function findById(string $id): ?ScannerDevice { return ScannerDevice::where('id', $id)->first(); }
    public function create(array $data): ScannerDevice { return ScannerDevice::create($data); }
    public function update(ScannerDevice $device, array $data): ScannerDevice { $device->update($data); return $device->refresh(); }
    public function delete(ScannerDevice $device): bool { return (bool) $device->delete(); }
}
