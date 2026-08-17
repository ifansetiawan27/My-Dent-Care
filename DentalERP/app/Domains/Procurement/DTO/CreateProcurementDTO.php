<?php

declare(strict_types=1);

namespace App\Domains\Procurement\DTO;

final readonly class CreateProcurementDTO
{
    public function __construct(
        public string $orderNumber,
        public string $orderDate,
        public string $organizationId,
        public ?string $supplierId = null,
        public ?string $branchId = null,
        public ?string $expectedDate = null,
        public ?string $totalAmount = null,
        public ?array $items = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'order_number'    => $this->orderNumber,
            'order_date'      => $this->orderDate,
            'organization_id' => $this->organizationId,
            'supplier_id'     => $this->supplierId,
            'branch_id'       => $this->branchId,
            'expected_date'   => $this->expectedDate,
            'total_amount'    => $this->totalAmount ?? '0',
            'items'           => $this->items,
            'notes'           => $this->notes,
            'status'          => 'pending',
        ];
    }
}