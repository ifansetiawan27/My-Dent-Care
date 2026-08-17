<?php

declare(strict_types=1);

namespace App\Domains\Procurement\DTO;

final readonly class UpdateProcurementDTO
{
    public function __construct(
        public ?string $orderNumber = null,
        public ?string $status = null,
        public ?string $supplierId = null,
        public ?string $branchId = null,
        public ?string $orderDate = null,
        public ?string $expectedDate = null,
        public ?string $totalAmount = null,
        public ?array $items = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'order_number'  => $this->orderNumber,
            'status'        => $this->status,
            'supplier_id'   => $this->supplierId,
            'branch_id'     => $this->branchId,
            'order_date'    => $this->orderDate,
            'expected_date' => $this->expectedDate,
            'total_amount'  => $this->totalAmount,
            'items'         => $this->items,
            'notes'         => $this->notes,
        ], fn ($v) => $v !== null);
    }
}