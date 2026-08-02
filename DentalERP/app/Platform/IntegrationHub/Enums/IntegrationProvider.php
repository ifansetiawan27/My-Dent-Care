<?php

declare(strict_types=1);

namespace App\Platform\IntegrationHub\Enums;

/**
 * IntegrationProvider
 *
 * External systems the platform integrates with through the Integration Hub.
 * Each provider is implemented as a connector behind IntegrationConnectorInterface.
 */
enum IntegrationProvider: string
{
    case SatuSehat     = 'satusehat';
    case Bpjs          = 'bpjs';
    case Insurance     = 'insurance';
    case WhatsApp      = 'whatsapp';
    case Sms           = 'sms';
    case PaymentGateway = 'payment_gateway';
    case Pacs          = 'pacs';
    case DentalScanner = 'dental_scanner';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SatuSehat      => 'SATUSEHAT',
            self::Bpjs           => 'BPJS',
            self::Insurance      => 'Insurance',
            self::WhatsApp       => 'WhatsApp',
            self::Sms            => 'SMS Gateway',
            self::PaymentGateway => 'Payment Gateway',
            self::Pacs           => 'PACS',
            self::DentalScanner  => 'Dental Scanner',
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
