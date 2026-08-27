<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Interfaces;
use App\Domains\Scanner\DTO\CreateScanSessionDTO;
use App\Domains\Scanner\DTO\UpdateScanSessionDTO;
use App\Domains\Scanner\Models\ScanSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface ScanSessionServiceInterface {
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id): ScanSession;
    public function create(CreateScanSessionDTO $dto): ScanSession;
    public function update(string $id, UpdateScanSessionDTO $dto): ScanSession;
    public function delete(string $id): bool;
    public function completeSession(string $id): ScanSession;
    public function failSession(string $id, string $reason): ScanSession;
}
