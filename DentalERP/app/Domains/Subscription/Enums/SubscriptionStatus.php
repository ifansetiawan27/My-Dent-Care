<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Enums;
enum SubscriptionStatus: string {
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Grace = 'grace';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    public function label(): string { return match($this) {
        self::Trial => 'Trial', self::Active => 'Active', self::PastDue => 'Past Due',
        self::Grace => 'Grace', self::Expired => 'Expired', self::Cancelled => 'Cancelled'
    };}
    public static function values(): array { return array_column(self::cases(),'value'); }
    public static function activeStates(): array { return [self::Trial, self::Active, self::PastDue, self::Grace]; }
    public static function restrictedStates(): array { return [self::Expired, self::Cancelled]; }
}