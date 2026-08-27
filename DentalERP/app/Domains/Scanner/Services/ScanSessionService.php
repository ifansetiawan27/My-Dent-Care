<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Services;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Scanner\DTO\CreateScanSessionDTO;
use App\Domains\Scanner\DTO\UpdateScanSessionDTO;
use App\Domains\Scanner\Enums\ScanSessionStatus;
use App\Domains\Scanner\Interfaces\ScanSessionRepositoryInterface;
use App\Domains\Scanner\Interfaces\ScanSessionServiceInterface;
use App\Domains\Scanner\Models\ScanSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class ScanSessionService implements ScanSessionServiceInterface {
    public function __construct(private readonly ScanSessionRepositoryInterface $repository) {}
    public function paginate(array $filters): LengthAwarePaginator { return $this->repository->paginate($filters); }
    public function findById(string $id): ScanSession {
        $session = $this->repository->findById($id);
        if (!$session) { throw new NotFoundException('Scan session not found.'); }
        return $session;
    }
    public function create(CreateScanSessionDTO $dto): ScanSession {
        $data = $dto->toArray();
        $data['session_number'] = $this->generateSessionNumber();
        $data['started_at'] = now();
        return DB::transaction(fn(): ScanSession => $this->repository->create($data));
    }
    public function update(string $id, UpdateScanSessionDTO $dto): ScanSession {
        $session = $this->findById($id);
        $data = $dto->toArray();
        if (isset($data['status'])) {
            $this->validateStatusTransition(
                ScanSessionStatus::from($session->status),
                ScanSessionStatus::from($data['status']),
            );
        }
        return DB::transaction(fn(): ScanSession => $this->repository->update($session, $data));
    }
    public function delete(string $id): bool {
        $session = $this->findById($id);
        if (ScanSessionStatus::from($session->status)->isTerminal()) {
            throw new BusinessException('Cannot delete a completed or failed scan session.');
        }
        return $this->repository->delete($session);
    }
    public function completeSession(string $id): ScanSession {
        $session = $this->findById($id);
        if (ScanSessionStatus::from($session->status)->isTerminal()) {
            throw new BusinessException('Cannot complete a session that is already terminal.');
        }
        return DB::transaction(function () use ($session): ScanSession {
            $this->repository->update($session, ['status' => 'completed', 'completed_at' => now()]);
            return $session->refresh();
        });
    }
    public function failSession(string $id, string $reason): ScanSession {
        $session = $this->findById($id);
        if (ScanSessionStatus::from($session->status)->isTerminal()) {
            throw new BusinessException('Cannot fail a session that is already terminal.');
        }
        return DB::transaction(function () use ($session, $reason): ScanSession {
            $notes = trim(($session->notes ?: '') . "\nFailed: " . $reason);
            $this->repository->update($session, ['status' => 'failed', 'notes' => $notes]);
            return $session->refresh();
        });
    }
    private function generateSessionNumber(): string {
        $prefix = 'SS-' . now()->format('Y') . '-';
        $last = ScanSession::where('session_number', 'LIKE', $prefix . '%')->orderBy('session_number', 'desc')->first();
        $seq = $last ? (int)substr($last->session_number, -6) + 1 : 1;
        return $prefix . str_pad((string)$seq, 6, '0', STR_PAD_LEFT);
    }
    private function validateStatusTransition(ScanSessionStatus $current, ScanSessionStatus $new): void {
        if ($current === $new) { return; }
        if ($current->isTerminal()) {
            throw new BusinessException("Cannot update a scan session that is already '{$current->value}'.");
        }
    }
}
