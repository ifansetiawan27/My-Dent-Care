<?php

declare(strict_types=1);

namespace App\Platform\PaymentGateway\DTO;

use App\Platform\PaymentGateway\Enums\PaymentTransactionStatus;

/**
 * PaymentResultDTO
 *
 * Immutable value object describing the outcome of a payment operation.
 * Normalizes provider-specific responses into a single canonical shape.
 */
final readonly class PaymentResultDTO
{
    /**
     * @param  bool                     $success        Whether the operation succeeded.
     * @param  PaymentTransactionStatus $status         Normalized transaction status.
     * @param  string                   $referenceId    Internal reference echoed back.
     * @param  string|null              $providerRef    Provider transaction identifier.
     * @param  string|null              $paymentUrl     Hosted payment / redirect URL.
     * @param  string|null              $errorMessage   Error message when unsuccessful.
     * @param  array<string, mixed>     $raw            Raw provider response.
     */
    public function __construct(
        public bool                     $success,
        public PaymentTransactionStatus $status,
        public string                   $referenceId,
        public ?string                  $providerRef  = null,
        public ?string                  $paymentUrl   = null,
        public ?string                  $errorMessage = null,
        public array                    $raw          = [],
    ) {}

    /**
     * Serialize to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success'      => $this->success,
            'status'       => $this->status->value,
            'reference_id' => $this->referenceId,
            'provider_ref' => $this->providerRef,
            'payment_url'  => $this->paymentUrl,
            'error_message'=> $this->errorMessage,
        ];
    }
}
