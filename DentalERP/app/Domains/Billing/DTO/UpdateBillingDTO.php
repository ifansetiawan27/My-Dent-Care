<?php

declare(strict_types=1);

namespace App\Domains\Billing\DTO;

final readonly class UpdateBillingDTO
{
    public function __construct(
        public ?string $patientId = null,
        public ?string $totalAmount = null,
        public ?string $paidAmount = null,
        public ?string $status = null,
        public ?string $dueDate = null,
        public ?array $items = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'patient_id'   => $this->patientId,
            'total_amount' => $this->totalAmount,
            'paid_amount'  => $this->paidAmount,
            'status'       => $this->status,
            'due_date'     => $this->dueDate,
            'items'        => $this->items,
            'notes'        => $this->notes,
        ], fn ($v) => $v !== null);
    }
}