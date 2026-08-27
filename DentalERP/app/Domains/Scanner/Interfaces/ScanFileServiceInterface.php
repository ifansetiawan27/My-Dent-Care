<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Interfaces;
use App\Domains\Scanner\DTO\CreateScanFileDTO;
use App\Domains\Scanner\Models\ScanFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface ScanFileServiceInterface {
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id): ScanFile;
    public function create(CreateScanFileDTO $dto): ScanFile;
    public function update(string $id, array $data): ScanFile;
    public function delete(string $id): bool;
    public function markProcessed(string $id): ScanFile;
}
