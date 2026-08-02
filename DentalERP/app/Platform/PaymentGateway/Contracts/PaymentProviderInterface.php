<?php

declare(strict_types=1);

namespace App\Platform\PaymentGateway\Contracts;

use App\Platform\PaymentGateway\DTO\PaymentRequestDTO;
use App\Platform\PaymentGateway\DTO\PaymentResultDTO;
use App\Platform\PaymentGateway\Enums\PaymentProvider;

/**
 * PaymentProviderInterface
 *
 * Contract for a single payment provider driver (Midtrans, Xendit, DOKU, Manual).
 * The Payment Gateway resolves the correct driver per provider and delegates.
 *
 * Open/Closed: adding a new provider means adding a new driver implementing this
 * interface — no existing code is modified.
 */
interface PaymentProviderInterface
{
    /**
     * The provider this driver handles.
     */
    public function provider(): PaymentProvider;

    /**
     * Create a charge with the provider.
     *
     * @param  PaymentRequestDTO $request
     * @return PaymentResultDTO
     */
    public function charge(PaymentRequestDTO $request): PaymentResultDTO;

    /**
     * Query the current status of a transaction by provider reference.
     *
     * @param  string $providerRef
     * @return PaymentResultDTO
     */
    public function status(string $providerRef): PaymentResultDTO;

    /**
     * Refund a settled transaction, fully or partially.
     *
     * @param  string   $providerRef
     * @param  int|null $amount  Amount in smallest currency unit; null for full refund.
     * @return PaymentResultDTO
     */
    public function refund(string $providerRef, ?int $amount = null): PaymentResultDTO;

    /**
     * Verify and parse a provider callback/webhook into a normalized result.
     *
     * @param  array<string, mixed> $payload
     * @return PaymentResultDTO
     */
    public function handleCallback(array $payload): PaymentResultDTO;
}
