<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Interfaces;
use App\Domains\Scanner\Models\ScanFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface ScanFileRepositoryInterface {
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id): ?ScanFile;
    public function create(array $data): ScanFile;
    public function update(ScanFile $file, array $data): ScanFile;
    public function delete(ScanFile $file): bool;
}
