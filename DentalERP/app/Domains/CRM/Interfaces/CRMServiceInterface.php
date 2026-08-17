<?php

declare(strict_types=1);

namespace App\Domains\CRM\Interfaces;

use App\Domains\CRM\DTO\CreateCRMDTO;
use App\Domains\CRM\DTO\UpdateCRMDTO;
use App\Domains\CRM\Models\CRM;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CRMServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): CRM;
    public function create(CreateCRMDTO $dto): CRM;
    public function update(string $id, UpdateCRMDTO $dto, string $organizationId): CRM;
    public function delete(string $id, string $organizationId): bool;
}