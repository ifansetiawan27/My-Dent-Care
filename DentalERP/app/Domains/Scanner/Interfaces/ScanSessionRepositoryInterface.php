<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Interfaces;
use App\Domains\Scanner\Models\ScanSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface ScanSessionRepositoryInterface {
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id): ?ScanSession;
    public function create(array $data): ScanSession;
    public function update(ScanSession $session, array $data): ScanSession;
    public function delete(ScanSession $session): bool;
}
