<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Repositories;
use App\Domains\Scanner\Interfaces\ScanFileRepositoryInterface;
use App\Domains\Scanner\Models\ScanFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
final class ScanFileRepository implements ScanFileRepositoryInterface {
    public function paginate(array $filters): LengthAwarePaginator {
        $query = ScanFile::query();
        if (!empty($filters['scan_session_id'])) { $query->where('scan_session_id', $filters['scan_session_id']); }
        if (!empty($filters['file_format'])) { $query->where('file_format', $filters['file_format']); }
        if (!empty($filters['processing_status'])) { $query->where('processing_status', $filters['processing_status']); }
        if (!empty($filters['is_primary'])) { $query->where('is_primary', true); }
        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'file_size']) ? $filters['sort_by'] : 'created_at';
        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')->paginate(min((int)($filters['per_page'] ?? 20), 100));
    }
    public function findById(string $id): ?ScanFile { return ScanFile::where('id', $id)->with('scanSession')->first(); }
    public function create(array $data): ScanFile { return ScanFile::create($data); }
    public function update(ScanFile $file, array $data): ScanFile { $file->update($data); return $file->refresh(); }
    public function delete(ScanFile $file): bool { return (bool) $file->delete(); }
}
