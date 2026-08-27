<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Services;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Scanner\DTO\CreateScannerDeviceDTO;
use App\Domains\Scanner\DTO\UpdateScannerDeviceDTO;
use App\Domains\Scanner\Interfaces\ScannerDeviceRepositoryInterface;
use App\Domains\Scanner\Interfaces\ScannerDeviceServiceInterface;
use App\Domains\Scanner\Models\ScannerDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class ScannerDeviceService implements ScannerDeviceServiceInterface {
    public function __construct(private readonly ScannerDeviceRepositoryInterface $repository) {}
    public function paginate(array $filters): LengthAwarePaginator { return $this->repository->paginate($filters); }
    public function findById(string $id): ScannerDevice {
        $device = $this->repository->findById($id);
        if (!$device) { throw new NotFoundException('Scanner device not found.'); }
        return $device;
    }
    public function create(CreateScannerDeviceDTO $dto): ScannerDevice {
        return DB::transaction(fn(): ScannerDevice => $this->repository->create($dto->toArray()));
    }
    public function update(string $id, UpdateScannerDeviceDTO $dto): ScannerDevice {
        $device = $this->findById($id);
        return DB::transaction(fn(): ScannerDevice => $this->repository->update($device, $dto->toArray()));
    }
    public function delete(string $id): bool {
        $device = $this->findById($id);
        return $this->repository->delete($device);
    }
    public function calibrateDevice(string $id): ScannerDevice {
        $device = $this->findById($id);
        return DB::transaction(function () use ($device): ScannerDevice {
            $this->repository->update($device, ['last_calibration_at' => now()]);
            return $device->refresh();
        });
    }
}
