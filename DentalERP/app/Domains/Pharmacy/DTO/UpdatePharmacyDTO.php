<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\DTO;

final readonly class UpdatePharmacyDTO
{
    public function __construct(
        public ?string $drugCode = null,
        public ?string $name = null,
        public ?string $branchId = null,
        public ?string $category = null,
        public ?string $quantity = null,
        public ?string $unit = null,
        public ?string $unitPrice = null,
        public ?string $expiryDate = null,
        public ?string $batchNumber = null,
        public ?bool $isActive = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'drug_code'    => $this->drugCode,
            'name'         => $this->name,
            'branch_id'    => $this->branchId,
            'category'     => $this->category,
            'quantity'     => $this->quantity,
            'unit'         => $this->unit,
            'unit_price'   => $this->unitPrice,
            'expiry_date'  => $this->expiryDate,
            'batch_number' => $this->batchNumber,
            'is_active'    => $this->isActive,
        ], fn ($v) => $v !== null);
    }
}