<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Radiology\DTO\CreateRadiologyOrderDTO;
use App\Domains\Radiology\DTO\UpdateRadiologyOrderDTO;
use App\Domains\Radiology\Enums\RadiologyOrderStatus;
use App\Domains\Radiology\Interfaces\RadiologyOrderRepositoryInterface;
use App\Domains\Radiology\Interfaces\RadiologyOrderServiceInterface;
use App\Domains\Radiology\Models\RadiologyOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class RadiologyOrderService implements RadiologyOrderServiceInterface
{
    public function __construct(
        private readonly RadiologyOrderRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): RadiologyOrder
    {
        $order = $this->repository->findById($id, $organizationId);
        if (! $order) {
            throw new NotFoundException('Radiology order not found.');
        }
        return $order;
    }

    public function create(CreateRadiologyOrderDTO $dto): RadiologyOrder
    {
        $data = $dto->toArray();
        $data['order_number'] = $this->generateOrderNumber();

        return DB::transaction(fn (): RadiologyOrder => $this->repository->create($data));
    }

    public function update(string $id, UpdateRadiologyOrderDTO $dto, string $organizationId): RadiologyOrder
    {
        $order = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                RadiologyOrderStatus::from($order->status),
                RadiologyOrderStatus::from($data['status']),
            );
        }

        return DB::transaction(fn (): RadiologyOrder => $this->repository->update($order, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $order = $this->findById($id, $organizationId);

        if (RadiologyOrderStatus::from($order->status)->isTerminal()) {
            throw new BusinessException('Cannot delete a completed or cancelled radiology order.');
        }

        return $this->repository->delete($order);
    }

    public function completeOrder(string $id, string $organizationId): RadiologyOrder
    {
        $order = $this->findById($id, $organizationId);

        if (RadiologyOrderStatus::from($order->status)->isTerminal()) {
            throw new BusinessException(
                "Cannot complete an order that is already '{$order->status}'.",
            );
        }

        $data = [
            'status'       => RadiologyOrderStatus::Completed->value,
            'completed_at' => now(),
        ];

        return DB::transaction(fn (): RadiologyOrder => $this->repository->update($order, $data));
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'RO-' . now()->format('Y') . '-';
        $last = RadiologyOrder::where('order_number', 'LIKE', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        $seq = $last ? (int) substr($last->order_number, -6) + 1 : 1;

        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    private function validateStatusTransition(RadiologyOrderStatus $current, RadiologyOrderStatus $new): void
    {
        if ($current === $new) {
            return;
        }

        if ($current->isTerminal()) {
            throw new BusinessException(
                "Cannot update an order that is already in '{$current->value}' status.",
            );
        }

        $allowed = match ($current) {
            RadiologyOrderStatus::Ordered    => [RadiologyOrderStatus::InProgress, RadiologyOrderStatus::Cancelled],
            RadiologyOrderStatus::InProgress => [RadiologyOrderStatus::Completed, RadiologyOrderStatus::Cancelled],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition order from '{$current->value}' to '{$new->value}'.",
            );
        }
    }
}
