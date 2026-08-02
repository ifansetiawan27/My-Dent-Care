<?php

declare(strict_types=1);

namespace App\Platform\PaymentGateway\DTO;

use App\Platform\PaymentGateway\Enums\PaymentProvider;

/**
 * PaymentRequestDTO
 *
 * Immutable value object describing a payment charge request.
 * Domains (e.g. Billing) construct this and pass it to PaymentGatewayServiceInterface.
 * Monetary amounts are integers in the smallest currency unit (e.g. cents/sen).
 */
final readonly class PaymentRequestDTO
{
    /**
     * @param  PaymentProvider       $provider        Target payment provider.
     * @param  string                $referenceId     Internal reference (e.g. invoice UUID).
     * @param  int                   $amount          Amount in the smallest currency unit.
     * @param  string                $currency        ISO 4217 currency code (e.g. 'IDR').
     * @param  string                $organizationId  Tenant organization UUID.
     * @param  string|null           $customerName    Payer name.
     * @param  string|null           $customerEmail   Payer email.
     * @param  string|null           $customerPhone   Payer phone.
     * @param  string|null           $description     Human-readable description.
     * @param  array<string, mixed>  $metadata        Extra provider metadata.
     * @param  string|null           $callbackUrl     Redirect/callback URL after payment.
     */
    public function __construct(
        public PaymentProvider $provider,
        public string          $referenceId,
        public int             $amount,
        public string          $currency,
        public string          $organizationId,
        public ?string         $customerName  = null,
        public ?string         $customerEmail = null,
        public ?string         $customerPhone = null,
        public ?string         $description   = null,
        public array           $metadata      = [],
        public ?string         $callbackUrl   = null,
    ) {}
}
