<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Services;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Scanner\DTO\CreateScanFileDTO;
use App\Domains\Scanner\Interfaces\ScanFileRepositoryInterface;
use App\Domains\Scanner\Interfaces\ScanFileServiceInterface;
use App\Domains\Scanner\Models\ScanFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class ScanFileService implements ScanFileServiceInterface {
    public function __construct(private readonly ScanFileRepositoryInterface $repository) {}
    public function paginate(array $filters): LengthAwarePaginator { return $this->repository->paginate($filters); }
    public function findById(string $id): ScanFile {
        $file = $this->repository->findById($id);
        if (!$file) { throw new NotFoundException('Scan file not found.'); }
        return $file;
    }
    public function create(CreateScanFileDTO $dto): ScanFile {
        return DB::transaction(fn(): ScanFile => $this->repository->create($dto->toArray()));
    }
    public function update(string $id, array $data): ScanFile {
        $file = $this->findById($id);
        return DB::transaction(fn(): ScanFile => $this->repository->update($file, $data));
    }
    public function delete(string $id): bool {
        $file = $this->findById($id);
        return $this->repository->delete($file);
    }
    public function markProcessed(string $id): ScanFile {
        $file = $this->findById($id);
        return DB::transaction(function () use ($file): ScanFile {
            $this->repository->update($file, ['processing_status' => 'completed']);
            return $file->refresh();
        });
    }
}
