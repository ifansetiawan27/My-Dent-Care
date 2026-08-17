<?php

declare(strict_types=1);

namespace App\Domains\HR\DTO;

final readonly class UpdateHRDTO
{
    public function __construct(
        public ?string $recordType = null,
        public ?string $status = null,
        public ?string $employeeId = null,
        public ?string $effectiveDate = null,
        public ?string $endDate = null,
        public ?array $data = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'record_type'    => $this->recordType,
            'status'         => $this->status,
            'employee_id'    => $this->employeeId,
            'effective_date' => $this->effectiveDate,
            'end_date'       => $this->endDate,
            'data'           => $this->data,
            'notes'          => $this->notes,
        ], fn ($v) => $v !== null);
    }
}