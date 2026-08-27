<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Repositories;
use App\Domains\Scanner\Interfaces\ScanSessionRepositoryInterface;
use App\Domains\Scanner\Models\ScanSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
final class ScanSessionRepository implements ScanSessionRepositoryInterface {
    public function paginate(array $filters): LengthAwarePaginator {
        $query = ScanSession::query();
        if (!empty($filters['patient_id'])) { $query->where('patient_id', $filters['patient_id']); }
        if (!empty($filters['doctor_id'])) { $query->where('doctor_id', $filters['doctor_id']); }
        if (!empty($filters['device_id'])) { $query->where('device_id', $filters['device_id']); }
        if (!empty($filters['status'])) { $query->where('status', $filters['status']); }
        if (!empty($filters['scan_type'])) { $query->where('scan_type', $filters['scan_type']); }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('session_number', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('notes', 'ILIKE', "%{$filters['search']}%");
            });
        }
        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'started_at', 'completed_at']) ? $filters['sort_by'] : 'created_at';
        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')->paginate(min((int)($filters['per_page'] ?? 20), 100));
    }
    public function findById(string $id): ?ScanSession { return ScanSession::where('id', $id)->with(['device', 'scanFiles'])->first(); }
    public function create(array $data): ScanSession { return ScanSession::create($data); }
    public function update(ScanSession $session, array $data): ScanSession { $session->update($data); return $session->refresh(); }
    public function delete(ScanSession $session): bool { return (bool) $session->delete(); }
}
