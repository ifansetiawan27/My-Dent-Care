<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Enums;
enum SubscriptionTrigger: string {
    case TrialStarted = 'trial_started';
    case TrialExpired = 'trial_expired';
    case PaymentActivated = 'payment_activated';
    case RenewalPaymentSucceeded = 'renewal_payment_succeeded';
    case RenewalPaymentFailed = 'renewal_payment_failed';
    case PaymentRetryFailed = 'payment_retry_failed';
    case GraceStarted = 'grace_started';
    case GraceExpired = 'grace_expired';
    case ReactivationSucceeded = 'reactivation_succeeded';
    case SubscriptionCancelled = 'subscription_cancelled';
    public function label(): string { return match($this) {
        self::TrialStarted => 'Trial Started', self::TrialExpired => 'Trial Expired',
        self::PaymentActivated => 'Payment Activated', self::RenewalPaymentSucceeded => 'Renewal Payment Succeeded',
        self::RenewalPaymentFailed => 'Renewal Payment Failed', self::PaymentRetryFailed => 'Payment Retry Failed',
        self::GraceStarted => 'Grace Started', self::GraceExpired => 'Grace Expired',
        self::ReactivationSucceeded => 'Reactivation Succeeded', self::SubscriptionCancelled => 'Cancelled'
    };}
    public static function values(): array { return array_column(self::cases(),'value'); }
}