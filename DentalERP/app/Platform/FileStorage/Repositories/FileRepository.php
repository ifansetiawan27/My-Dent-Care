<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Repositories;

use App\Platform\FileStorage\Models\File;
use Illuminate\Database\Eloquent\Collection;

class FileRepository
{
    public function __construct(
        private readonly File $model
    ) {
    }

    public function create(array $data): File
    {
        return $this->model->create($data);
    }

    public function findById(string $id): ?File
    {
        return $this->model->find($id);
    }

    public function findByPath(string $path): ?File
    {
        return $this->model
            ->where('path', $path)
            ->whereNull('deleted_at')
            ->first();
    }

    public function findByHash(string $hash, string $organizationId): ?File
    {
        return $this->model
            ->where('hash', $hash)
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->first();
    }

    public function findByOwner(string $fileableType, string $fileableId): Collection
    {
        return $this->model
            ->where('fileable_type', $fileableType)
            ->where('fileable_id', $fileableId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByOrganization(string $organizationId, ?string $folder = null, int $limit = 50): Collection
    {
        $query = $this->model
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at');

        if ($folder) {
            $query->where('folder', $folder);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function update(string $id, array $data): bool
    {
        $file = $this->findById($id);
        
        if (!$file) {
            return false;
        }

        return $file->update($data);
    }

    public function delete(string $id): bool
    {
        $file = $this->findById($id);
        
        if (!$file) {
            return false;
        }

        return $file->delete();
    }

    public function forceDelete(string $id): bool
    {
        $file = $this->model->withTrashed()->find($id);
        
        if (!$file) {
            return false;
        }

        return $file->forceDelete();
    }
}
