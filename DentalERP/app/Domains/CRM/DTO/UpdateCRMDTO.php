<?php

declare(strict_types=1);

namespace App\Domains\CRM\DTO;

final readonly class UpdateCRMDTO
{
    public function __construct(
        public ?string $contactType = null,
        public ?string $status = null,
        public ?string $patientId = null,
        public ?string $channel = null,
        public ?string $subject = null,
        public ?string $message = null,
        public ?string $followUpDate = null,
        public ?string $resolution = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'contact_type'   => $this->contactType,
            'status'         => $this->status,
            'patient_id'     => $this->patientId,
            'channel'        => $this->channel,
            'subject'        => $this->subject,
            'message'        => $this->message,
            'follow_up_date' => $this->followUpDate,
            'resolution'     => $this->resolution,
        ], fn ($v) => $v !== null);
    }
}